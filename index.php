<?php include('includes/header.php'); ?>
<style>
  .hero {
  background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('img/index_bg.jpg') no-repeat center center/cover;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.hero h1 {
  font-size: 4rem;
  animation: fadeInDown 1s ease-in-out;
}

.hero p {
  font-size: 1.5rem;
  animation: fadeInUp 1s ease-in-out 0.5s;
  animation-fill-mode: both;
}

.btn-danger {
  transition: transform 0.3s ease, background-color 0.3s ease;
}

.btn-danger:hover {
  transform: scale(1.1);
  background-color: #c82333;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

  .features .card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  .features .card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
  }
  .testimonials {
    background-color: #f8f9fa;
  }
  .testimonials .card {
    border: none;
    background-color: #fff;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  }
  @keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-50px); }
    to { opacity: 1; transform: translateY(0); }
  }
  @keyframes fadeInUp {
    from { opacity: 0; transform: translateY(50px); }
    to { opacity: 1; transform: translateY(0); }
  }
</style>

<section class="hero text-white text-center d-flex align-items-center justify-content-center">
  <div class="container">
    <img src="img/logo.png" alt="DCSP Logo" class="hero-logo" style="width: 290px; animation:
      fadeInScale 1s ease-in-out;">
    <h1 class="display-4 fw-bold">Welcome to RMS</h1>

    <!-- Initial Button -->
    <button onclick="showRoleOptions()" class="btn btn-danger btn-lg mt-3" id="getStartedBtn">Get Started</button>

    <!-- Role Options (Hidden by default) -->
    <div id="roleOptions" class="mt-4 d-none">
  <div class="col-md-6 mx-auto text-center">
    <a href="admin/admin_login.php" class="btn btn-outline-primary btn-lg mb-2 w-100">As Administrator</a>
    <a href="teacher/t_login.php" class="btn btn-outline-success btn-lg mb-2 w-100">As Teacher</a>
    <a href="student/s_login.php" class="btn btn-outline-secondary btn-lg w-100">As Student</a>
    <a href="helpsupport/s_help_support.php" class="btn btn-outline-secondary btn-lg mt-3">Help/support</a>


  </div>
</div>

  </div>
</section>


<script>
  function showRoleOptions() {
    document.getElementById('getStartedBtn').classList.add('d-none');
    document.getElementById('roleOptions').classList.remove('d-none');
  }
</script>


<section class="features py-5 bg-light text-center" id="features">
  <div class="container">
    <h2 class="mb-4">Key Features</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 shadow">
          <div class="card-body">
            <span class="material-icons text-primary display-5 mb-3">schedule</span>
            <h5 class="card-title">Automated Scheduling</h5>
            <p class="card-text">Create conflict-free timetables in seconds.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow">
          <div class="card-body">
            <span class="material-icons text-danger display-5 mb-3">sync</span>
            <h5 class="card-title">Real-Time Updates</h5>
            <p class="card-text">Get notified on changes instantly.</p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow">
          <div class="card-body">
            <span class="material-icons text-success display-5 mb-3">people</span>
            <h5 class="card-title">User-Friendly</h5>
            <p class="card-text">Simple, clean, and responsive interface.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="testimonials py-5 text-center">
  <div class="container">
    <h2 class="mb-4">What Our Users Say</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <p class="card-text">"RMS has transformed how we manage schedules. It's so intuitive!"</p>
            <h6 class="card-subtitle text-muted mt-3">— Shreya Khatri, Educator</h6>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <p class="card-text">"The real-time updates save us so much time. Highly recommend!"</p>
            <h6 class="card-subtitle text-muted mt-3">— Santoshi Magar, Administrator</h6>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100">
          <div class="card-body">
            <p class="card-text">"A game-changer for our institution. Simple and effective."</p>
            <h6 class="card-subtitle text-muted mt-3">— Niticodes, Coordinator</h6>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include('includes/footer.php'); ?>