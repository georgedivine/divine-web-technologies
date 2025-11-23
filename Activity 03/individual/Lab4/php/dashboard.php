<?php
if (session_status() !== PHP_SESSION_NONE) {
    session_unset();
    session_destroy();
}

session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student'){
    header("Location: login_process.php");
    exit();
}

require 'config.php';


$student_id = $_SESSION['user_id'];

//get courses the student hasn't requested yet
$stmt = $conn->prepare("
    SELECT * FROM courses 
    WHERE id NOT IN (
        SELECT course_id FROM course_requests WHERE student_id = ?
    )
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);


//get student requests
$stmt2 = $conn->prepare("SELECT cr.id, c.name as course_name, cr.status 
                        FROM course_requests cr 
                        JOIN courses c ON cr.course_id = c.id 
                        WHERE cr.student_id = ?");
$stmt2->execute([$student_id]);
$requests = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>Student Dashboard</title>
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

<!--Template from W3Schools-->

<nav class="w3-sidebar w3-red w3-collapse w3-top w3-large w3-padding" style="z-index:3;width:250px;font-weight:bold;" id="mySidebar"><br>
  <div class="w3-container">
    <h3 class="w3-padding-64"><b>FI Portal</b></h3>
  </div>
  <div class="w3-bar-block">
    <a href="dashboard.php" class="w3-bar-item w3-button w3-hover-white">Dashboard</a> 
    <a href="logout.php" class="w3-bar-item w3-button w3-hover-white">Logout</a>
  </div>
</nav>


<div class="w3-overlay w3-hide-large" onclick="w3_close()" style="cursor:pointer" id="myOverlay"></div>


<div class="w3-main container">

  <h1 class="w3-xxxlarge w3-text-red">Student Dashboard</h1>
  <hr style="width:50px;border:5px solid red" class="w3-round">

  <div class="w3-container" style="margin-top:20px">
    <h2>Available Courses</h2>
    <div class="w3-row-padding w3-margin-top">
      <?php foreach($courses as $course): ?>
      <div class="w3-third w3-margin-bottom">
        <div class="w3-card w3-white w3-padding">
          <h4><?php echo htmlspecialchars($course['name']); ?></h4>
          <p><?php echo htmlspecialchars($course['description']); ?></p>
          <form method="POST" action="request_course.php">
            <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
            <button class="w3-button w3-red w3-block" type="submit">Request Join</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>


  <div class="w3-container" style="margin-top:40px">
    <h2>My Requests</h2>
    <table class="w3-table w3-bordered w3-striped w3-white w3-hoverable">
      <tr class="w3-red">
        <th>Course</th>
        <th>Status</th>
      </tr>
      <?php foreach($requests as $req): ?>
      <tr>
        <td><?php echo htmlspecialchars($req['course_name']); ?></td>
        <td><?php echo htmlspecialchars(ucfirst($req['status'])); ?></td>
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
