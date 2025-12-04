<?php
if (session_status() !== PHP_SESSION_NONE) {
    session_unset();
    session_destroy();
}

session_start();
require "config.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    header("Location: login_process.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];

//course creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_name'])) {
    $name = trim($_POST['course_name']);
    $desc = trim($_POST['course_desc']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO courses (name, description, faculty_id) VALUES (?, ?, ?)");
        $stmt->execute([$name, $desc, $faculty_id]);
        header("Location: faculty_dashboard.php");
        exit();
    }
}

//approval/rejection
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $request_id = (int)$_GET['id'];

    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE course_requests SET status = ? WHERE id = ?");
        $stmt->execute([$status, $request_id]);
        header("Location: faculty_dashboard.php");
        exit();
    }
}

//get faculty courses
$stmt = $conn->prepare("SELECT * FROM courses WHERE faculty_id = ?");
$stmt->execute([$faculty_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

//get student requests for faculty's courses
$stmt2 = $conn->prepare("
    SELECT cr.id, cr.student_id, u.email as student_email, c.name as course_name, cr.status
    FROM course_requests cr
    JOIN courses c ON cr.course_id = c.id
    JOIN users u ON cr.student_id = u.id
    WHERE c.faculty_id = ?
");
$stmt2->execute([$faculty_id]);
$requests = $stmt2->fetchAll(PDO::FETCH_ASSOC);

//get all sessions created by this faculty
$stmt3 = $conn->prepare("
    SELECT cs.id as session_id, c.name as course_name, cs.sessionDate, cs.attendanceCode
    FROM class_sessions cs
    JOIN courses c ON cs.courseId = c.id
    WHERE cs.facultyId = ?
    ORDER BY cs.sessionDate DESC
");
$stmt3->execute([$faculty_id]);
$sessions = $stmt3->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Faculty Dashboard</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
  <style>
  body,h1,h2,h3,h4,h5 {font-family: "Poppins", sans-serif}
  body {font-size:16px;}
  .container {margin-left:300px; padding:20px;}
  </style>
</head>
<body>

<!--Tenplate from W3Schools -->
<nav class="w3-sidebar w3-red w3-collapse w3-top w3-large w3-padding" style="z-index:3;width:250px;font-weight:bold;" id="mySidebar"><br>
  <div class="w3-container">
    <h3 class="w3-padding-64"><b>FI Portal</b></h3>
  </div>
  <div class="w3-bar-block">
    <a href="faculty_dashboard.php" class="w3-bar-item w3-button w3-hover-white">Dashboard</a> 
    <a href="logout.php" class="w3-bar-item w3-button w3-hover-white">Logout</a>
    <a href="create_session.php" class="w3-bar-item w3-button w3-hover-white">Create Class Session</a>
  </div>
</nav>

<div class="w3-overlay w3-hide-large" onclick="w3_close()" style="cursor:pointer" id="myOverlay"></div>

<div class="w3-main container">
  <?php
    if (isset($_SESSION['message'])) {
        //display attendance success or failure message
        $class = strpos($_SESSION['message'], 'error') !== false || strpos($_SESSION['message'], 'Incorrect') !== false ? 'w3-pale-red' : 'w3-pale-green';
        echo '<div class="w3-panel ' . $class . ' w3-border w3-round w3-padding">';
        echo htmlspecialchars($_SESSION['message']);
        echo '</div>';
        unset($_SESSION['message']);
    }
  ?>


  <h1 class="w3-xxxlarge w3-text-red">Faculty Dashboard</h1>
  <hr style="width:50px;border:5px solid red" class="w3-round">

  <div class="w3-container" style="margin-top:20px">
    <h2>Create New Course</h2>
    <form method="POST">
      <input class="w3-input w3-border w3-margin-bottom" type="text" name="course_name" placeholder="Course Name" required>
      <textarea class="w3-input w3-border w3-margin-bottom" name="course_desc" placeholder="Course Description"></textarea>
      <button class="w3-button w3-red" type="submit">Create Course</button>
    </form>
  </div>

  <div class="w3-container" style="margin-top:40px">
    <h2>My Courses</h2>
    <div class="w3-row-padding w3-margin-top">
      <?php foreach($courses as $course): ?>
      <div class="w3-third w3-margin-bottom">
        <div class="w3-card w3-white w3-padding">
          <h4><?php echo htmlspecialchars($course['name']); ?></h4>
          <p><?php echo htmlspecialchars($course['description']); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="w3-container" style="margin-top:40px">
    <h2>Student Requests</h2>
    <table class="w3-table w3-bordered w3-striped w3-white w3-hoverable">
      <tr class="w3-red">
        <th>Student</th>
        <th>Course</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
      <?php foreach($requests as $req): ?>
      <tr>
        <td><?php echo htmlspecialchars($req['student_email']); ?></td>
        <td><?php echo htmlspecialchars($req['course_name']); ?></td>
        <td><?php echo htmlspecialchars(ucfirst($req['status'])); ?></td>
        <td>
          <?php if ($req['status'] === 'pending'): ?>
            <a href="?action=approve&id=<?php echo $req['id']; ?>" class="w3-button w3-green w3-small">Approve</a>
            <a href="?action=reject&id=<?php echo $req['id']; ?>" class="w3-button w3-red w3-small">Reject</a>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>

  <div class="w3-container" style="margin-top:40px">
  <h2>Manage Class Sessions</h2>
  <table class="w3-table w3-bordered w3-striped w3-white w3-hoverable">
    <tr class="w3-red">
      <th>Session ID</th>
      <th>Course</th>
      <th>Date</th>
      <th>Attendance Code</th>
      <th>Actions</th>
    </tr>
    <?php foreach($sessions as $sess): ?>
    <tr>
      <td><?= $sess['session_id'] ?></td>
      <td><?= htmlspecialchars($sess['course_name']) ?></td>
      <td><?= $sess['sessionDate'] ?></td>
      <td><?= $sess['attendanceCode'] ?></td>
      <td>
        <a href="mark_attendance.php?session=<?= $sess['session_id'] ?>" class="w3-button w3-blue w3-small">Mark Attendance</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>


</div>

<script>
function w3_open() {
  document.getElementById("mySidebar").style.display = "block";
  document.getElementById("myOverlay").style.display = "block";
}
function w3_close() {
  document.getElementById("mySidebar").style.display = "none";
  document.getElementById("myOverlay").style.display = "none";
}
</script>

</body>
</html>
