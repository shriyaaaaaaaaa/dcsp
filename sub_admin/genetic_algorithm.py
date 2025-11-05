import json
import random
from datetime import datetime, timedelta

# --- Load data ---
with open('timetable_data.json', 'r') as f:
    data = json.load(f)

classes = data['classes']
teachers = data['teachers']

# --- Helper functions ---
def parse_time_str(t_str):
    return datetime.strptime(t_str, "%H:%M")

def format_time_str(dt):
    return dt.strftime("%H:%M")

# Map teachers by subject
subject_teachers_map = {}
for t in teachers:
    for sub in t['subjects']:
        subject_teachers_map.setdefault(sub, []).append(t)

# --- GA parameters ---
POP_SIZE = 30
GENERATIONS = 40
TOURNAMENT_SIZE = 5
MUTATION_RATE = 0.05
ELITISM = 2

# --- Chromosome representation ---
# List of dicts: {"class": name, "subject": subj, "teacher": t_name, "time": "HH:MM"}

def random_timetable():
    """Generate one random valid timetable."""
    timetable = []
    for c in classes:
        start_time = parse_time_str(c['start_time'])
        end_time = parse_time_str(c['end_time'])
        time_slots = []
        current_time = start_time
        while current_time < end_time:
            time_slots.append(current_time)
            current_time += timedelta(hours=1)

        for subject in c['subjects']:
            qualified = subject_teachers_map.get(subject, [])
            teacher = random.choice(qualified)['name'] if qualified else None
            time_choice = random.choice(time_slots)
            timetable.append({
                "class": c['class_name'],
                "subject": subject,
                "teacher": teacher,
                "time": format_time_str(time_choice)
            })
    return timetable

# --- Fitness function ---
def fitness(timetable):
    score = 1000  # Start high, subtract penalties
    seen_teacher_slots = set()
    seen_class_slots = set()  # NEW: track per-class collisions

    for entry in timetable:
        t_name = entry['teacher']
        subj = entry['subject']
        time = entry['time']
        cls = entry['class']

        # --- Teacher must be qualified ---
        qualified = any(t['name'] == t_name and subj in t['subjects'] for t in teachers)
        if not qualified:
            score -= 50

        # --- Teacher availability check ---
        teacher_obj = next((t for t in teachers if t['name'] == t_name), None)
        if teacher_obj:
            available = False
            t_dt = parse_time_str(time)
            for slot in teacher_obj['available']:
                start_str, end_str = slot.split('-')
                start_time = parse_time_str(start_str)
                end_time = parse_time_str(end_str)
                if start_time.time() <= t_dt.time() < end_time.time():
                    available = True
                    break
            if not available:
                score -= 30

        # --- Teacher double-booking check ---
        if (t_name, time) in seen_teacher_slots:
            score -= 100
        else:
            seen_teacher_slots.add((t_name, time))

        # --- Class double-booking check (NEW) ---
        if (cls, time) in seen_class_slots:
            score -= 100
        else:
            seen_class_slots.add((cls, time))

    return max(score, 0)

# --- Selection ---
def tournament_selection(population):
    competitors = random.sample(population, TOURNAMENT_SIZE)
    competitors.sort(key=lambda ind: fitness(ind), reverse=True)
    return competitors[0]

# --- Crossover ---
def crossover(parent1, parent2):
    point = random.randint(1, len(parent1) - 1)
    child1 = parent1[:point] + parent2[point:]
    child2 = parent2[:point] + parent1[point:]
    return child1, child2

# --- Mutation ---
def mutate(timetable):
    for i in range(len(timetable)):
        if random.random() < MUTATION_RATE:
            subj = timetable[i]['subject']
            cls_name = timetable[i]['class']
            qualified = subject_teachers_map.get(subj, [])
            if qualified:
                timetable[i]['teacher'] = random.choice(qualified)['name']

            # Change time but avoid collisions as much as possible
            c_data = next(c for c in classes if c['class_name'] == cls_name)
            start_time = parse_time_str(c_data['start_time'])
            end_time = parse_time_str(c_data['end_time'])
            time_slots = []
            current_time = start_time
            while current_time < end_time:
                time_slots.append(current_time)
                current_time += timedelta(hours=1)

            # Try to choose a free slot
            used_slots = {(entry['class'], entry['time']) for entry in timetable if entry != timetable[i]}
            free_slots = [slot for slot in time_slots if (cls_name, format_time_str(slot)) not in used_slots]
            if free_slots:
                timetable[i]['time'] = format_time_str(random.choice(free_slots))
            else:
                timetable[i]['time'] = format_time_str(random.choice(time_slots))
    return timetable

# --- GA main loop ---
population = [random_timetable() for _ in range(POP_SIZE)]

for gen in range(GENERATIONS):
    population.sort(key=lambda ind: fitness(ind), reverse=True)
    best_score = fitness(population[0])
    print(f"Gen {gen} - Best fitness: {best_score}")

    if best_score >= 1000:  # Perfect timetable found
        break

    new_population = population[:ELITISM]  # Elitism

    while len(new_population) < POP_SIZE:
        parent1 = tournament_selection(population)
        parent2 = tournament_selection(population)
        child1, child2 = crossover(parent1, parent2)
        child1 = mutate(child1)
        child2 = mutate(child2)
        new_population.extend([child1, child2])

    population = new_population[:POP_SIZE]

# --- Output best timetable ---
best_timetable = population[0]
with open('timetable.json', 'w') as f:
    json.dump(best_timetable, f, indent=2)

print("Best timetable saved to timetable.json")
