<?php
include 'includes/header.php';
?>

<h1>Organizations</h1>

<!-- Add new organization -->
<form action="add_organization.php" method="post">
    <input type="text" name="name" placeholder="Department Name" required>
    <input type="text" name="head" placeholder="Head of Department" required>
    <button type="submit">Add Organization</button>
</form>

<hr>

<!-- Table of organizations -->
<table  border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Head / Admin</th>
        <th>No. of Teachers</th>
        <th>Actions</th>
    </tr>
    <tr>
        <td>1</td>
        <td>Science</td>
        <td>Dr. Sharma</td>
        <td>5</td>
        <td>Edit | Delete</td>
    </tr>
    <tr>
        <td>2</td>
        <td>Mathematics</td>
        <td>Ms. Koirala</td>
        <td>4</td>
        <td>Edit | Delete</td>
    </tr>
</table>

<?php
include 'includes/footer.php';
?>