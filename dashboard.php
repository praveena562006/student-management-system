<?php

session_start();

include "db.php";


/* =========================================================
   ADMIN LOGIN PROTECTION
========================================================= */

if (!isset($_SESSION["admin"])) {

    header("Location: login.php");
    exit();
}


/* =========================================================
   TODAY
========================================================= */

$today = date("Y-m-d");


/* =========================================================
   TOTAL STUDENTS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM students"
);

$row = mysqli_fetch_assoc($result);

$total_students =
    intval($row["total"] ?? 0);


/* =========================================================
   TOTAL DEPARTMENTS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT department) AS total
     FROM students"
);

$row = mysqli_fetch_assoc($result);

$total_departments =
    intval($row["total"] ?? 0);


/* =========================================================
   ATTENDANCE SESSIONS TODAY
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM attendance_sessions
     WHERE attendance_date = '$today'"
);

$row = mysqli_fetch_assoc($result);

$sessions_today =
    intval($row["total"] ?? 0);


/* =========================================================
   PRESENT RECORDS TODAY
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ats.attendance_date = '$today'
     AND ar.status = 'Present'"
);

$row = mysqli_fetch_assoc($result);

$present_today =
    intval($row["total"] ?? 0);


/* =========================================================
   ABSENT RECORDS TODAY
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM attendance_records ar

     INNER JOIN attendance_sessions ats
        ON ar.session_id = ats.id

     WHERE ats.attendance_date = '$today'
     AND ar.status = 'Absent'"
);

$row = mysqli_fetch_assoc($result);

$absent_today =
    intval($row["total"] ?? 0);


/* =========================================================
   TODAY ATTENDANCE PERCENTAGE
========================================================= */

$total_today_records =
    $present_today + $absent_today;


$today_percentage =
    $total_today_records > 0
    ?
    round(
        ($present_today / $total_today_records) * 100,
        1
    )
    :
    0;


/* =========================================================
   OVERALL ATTENDANCE RECORDS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT

        COUNT(*) AS total_records,

        SUM(
            CASE
                WHEN status = 'Present'
                THEN 1
                ELSE 0
            END
        ) AS present_records,

        SUM(
            CASE
                WHEN status = 'Absent'
                THEN 1
                ELSE 0
            END
        ) AS absent_records

     FROM attendance_records"
);

$row = mysqli_fetch_assoc($result);


$total_attendance_records =
    intval($row["total_records"] ?? 0);

$total_present =
    intval($row["present_records"] ?? 0);

$total_absent =
    intval($row["absent_records"] ?? 0);


$overall_attendance_percentage =
    $total_attendance_records > 0
    ?
    round(
        ($total_present / $total_attendance_records) * 100,
        1
    )
    :
    0;


/* =========================================================
   STUDENTS BELOW 75% ATTENDANCE
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM
     (
        SELECT
            s.id,

            COUNT(ar.id) AS total_classes,

            SUM(
                CASE
                    WHEN ar.status = 'Present'
                    THEN 1
                    ELSE 0
                END
            ) AS present_classes

        FROM students s

        INNER JOIN attendance_records ar
            ON s.id = ar.student_id

        GROUP BY s.id

        HAVING
            COUNT(ar.id) > 0

            AND

            (
                SUM(
                    CASE
                        WHEN ar.status = 'Present'
                        THEN 1
                        ELSE 0
                    END
                )
                /
                COUNT(ar.id)
            ) * 100 < 75

     ) AS shortage_students"
);


if ($result) {

    $row =
        mysqli_fetch_assoc($result);

    $below_75 =
        intval($row["total"] ?? 0);

} else {

    $below_75 = 0;
}


/* =========================================================
   TOTAL MARK RECORDS
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM marks"
);

$row = mysqli_fetch_assoc($result);

$total_mark_records =
    intval($row["total"] ?? 0);


/* =========================================================
   STUDENTS WITH ALL RECORDED SUBJECTS PASSED
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total

     FROM
     (
        SELECT student_id

        FROM marks

        GROUP BY student_id

        HAVING
            COUNT(*) > 0

            AND

            SUM(
                CASE
                    WHEN LOWER(result) = 'fail'
                    THEN 1
                    ELSE 0
                END
            ) = 0

     ) AS passed_students"
);


if ($result) {

    $row =
        mysqli_fetch_assoc($result);

    $passed_students =
        intval($row["total"] ?? 0);

} else {

    $passed_students = 0;
}


/* =========================================================
   STUDENTS WITH AT LEAST ONE FAILED SUBJECT
========================================================= */

$result = mysqli_query(
    $conn,
    "SELECT COUNT(DISTINCT student_id) AS total

     FROM marks

     WHERE LOWER(result) = 'fail'"
);

$row = mysqli_fetch_assoc($result);

$failed_students =
    intval($row["total"] ?? 0);


/* =========================================================
   TOP PERFORMER
========================================================= */

$top_performer_result = mysqli_query(
    $conn,
    "SELECT
        s.name,
        s.roll_no,
        s.department,
        ROUND(AVG(m.total_marks), 1) AS average_marks
     FROM marks m
     INNER JOIN students s
        ON m.student_id = s.id
     GROUP BY s.id, s.name, s.roll_no, s.department
     ORDER BY average_marks DESC
     LIMIT 1"
);

$top_performer = $top_performer_result
    ? mysqli_fetch_assoc($top_performer_result)
    : null;


/* =========================================================
   DEPARTMENT DISTRIBUTION
========================================================= */

$department_distribution = mysqli_query(
    $conn,
    "SELECT department, COUNT(*) AS total
     FROM students
     GROUP BY department
     ORDER BY total DESC"
);


/* =========================================================
   RECENT ATTENDANCE SESSIONS
========================================================= */

$recent_sessions = mysqli_query(
    $conn,
    "SELECT

        ats.attendance_date,

        t.department,
        t.section,
        t.year,
        t.semester,
        t.subject_code,
        t.subject_name,
        t.start_period,
        t.end_period

     FROM attendance_sessions ats

     INNER JOIN timetable t
        ON ats.timetable_id = t.id

     ORDER BY
        ats.attendance_date DESC,
        ats.id DESC

     LIMIT 5"
);

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - EduTrack</title>
<link rel="stylesheet" href="css/style.css">
</head>

<body class="admin-dashboard-page">

<div class="navbar">
    <div class="dashboard-nav-brand">
        <span>🎓</span>
        <div>
            <strong>EduTrack</strong>
            <small>Academic Management System</small>
        </div>
    </div>

    <div class="dashboard-nav-user">
        <div class="dashboard-admin-avatar">
            <?php echo strtoupper(substr($_SESSION["admin"], 0, 1)); ?>
        </div>
        <div class="dashboard-admin-copy">
            <strong><?php echo htmlspecialchars($_SESSION["admin"]); ?></strong>
            <small>Administrator</small>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main-layout">

<?php
if (file_exists(__DIR__ . "/includes/admin_sidebar.php")) {
    include __DIR__ . "/includes/admin_sidebar.php";
} else {
?>
<div class="sidebar edutrack-sidebar">
    <div class="sidebar-brand-block">
        <div class="sidebar-brand-icon">🎓</div>
        <div><strong>EduTrack</strong><small>Academic Management</small></div>
    </div>

    <div class="sidebar-section-title">OVERVIEW</div>
    <div class="sidebar-group">
        <a href="dashboard.php" class="active"><span>🏠</span>Dashboard</a>
    </div>

    <div class="sidebar-section-title">STUDENT MANAGEMENT</div>
    <div class="sidebar-group">
        <a href="view_students.php"><span>👨‍🎓</span>All Students</a>
        <a href="add_student.php"><span>➕</span>Add Student</a>
    </div>

    <div class="sidebar-section-title">ACADEMICS</div>
    <div class="sidebar-group">
        <a href="attendance.php"><span>✓</span>Take Attendance</a>
        <a href="attendance_history.php"><span>📋</span>Attendance History</a>
        <a href="marks.php"><span>📝</span>Marks & Grades</a>
        <a href="subjects.php"><span>📚</span>Subjects</a>
    </div>

    <div class="sidebar-section-title">INSIGHTS</div>
    <div class="sidebar-group">
        <a href="reports.php"><span>📊</span>Reports & Analytics</a>
    </div>

    <div class="sidebar-section-title">ACCOUNT</div>
    <div class="sidebar-group sidebar-account">
        <a href="logout.php"><span>🚪</span>Logout</a>
    </div>
</div>
<?php } ?>

<main class="main-content dashboard-v2">

<section class="university-dashboard-hero dashboard-reveal">
    <div class="university-hero-copy">
        <span class="dashboard-eyebrow">ACADEMIC COMMAND CENTER</span>
        <h1>Good <?php
            $hour = intval(date("H"));
            echo $hour < 12 ? "Morning" : ($hour < 17 ? "Afternoon" : "Evening");
        ?>, <?php echo htmlspecialchars($_SESSION["admin"]); ?>.</h1>
        <p>Monitor student engagement, attendance and academic performance across EduTrack.</p>

        <div class="university-hero-actions">
            <a href="attendance.php" class="hero-action-primary">✓ Take Attendance</a>
            <a href="add_student.php" class="hero-action-secondary">＋ Add Student</a>
        </div>
    </div>

    <div class="university-hero-side">
        <div class="academic-year-chip">Academic Portal • <?php echo date("Y"); ?></div>
        <div class="hero-today-card">
            <small>TODAY</small>
            <strong><?php echo date("d"); ?></strong>
            <span><?php echo date("F Y"); ?></span>
            <em><?php echo date("l"); ?></em>
        </div>
    </div>
</section>

<section class="dashboard-v2-section dashboard-reveal">
    <div class="dashboard-v2-heading">
        <div>
            <span class="dashboard-eyebrow dark">OVERVIEW</span>
            <h2>Campus at a glance</h2>
        </div>
        <span class="live-data-label"><i></i> Live database</span>
    </div>

    <div class="primary-metric-grid">
        <article class="primary-metric-card">
            <div class="metric-top"><span class="metric-icon">👨‍🎓</span><span class="metric-tag">Students</span></div>
            <strong><?php echo $total_students; ?></strong>
            <p>Registered students</p>
        </article>

        <article class="primary-metric-card">
            <div class="metric-top"><span class="metric-icon">🏛️</span><span class="metric-tag">Campus</span></div>
            <strong><?php echo $total_departments; ?></strong>
            <p>Active departments</p>
        </article>

        <article class="primary-metric-card">
            <div class="metric-top"><span class="metric-icon">📈</span><span class="metric-tag">Attendance</span></div>
            <strong><?php echo $overall_attendance_percentage; ?>%</strong>
            <p>Overall attendance rate</p>
        </article>

        <article class="primary-metric-card attention-card">
            <div class="metric-top"><span class="metric-icon">⚠️</span><span class="metric-tag">Attention</span></div>
            <strong><?php echo $below_75; ?></strong>
            <p>Students below 75%</p>
        </article>
    </div>
</section>

<section class="dashboard-focus-grid dashboard-reveal">

    <article class="dashboard-v2-card attendance-focus-card">
        <div class="card-title-row">
            <div><span class="dashboard-eyebrow dark">ATTENDANCE</span><h2>Today's academic activity</h2></div>
            <a href="attendance_history.php">View history →</a>
        </div>

        <div class="attendance-focus-content">
            <div class="v2-attendance-ring" style="--attendance: <?php echo min(100, max(0, $today_percentage)); ?>;">
                <div><strong><?php echo $today_percentage; ?>%</strong><span>Today</span></div>
            </div>

            <div class="today-stat-list">
                <div><span>Sessions conducted</span><strong><?php echo $sessions_today; ?></strong></div>
                <div><span><i class="status-dot present"></i> Present records</span><strong><?php echo $present_today; ?></strong></div>
                <div><span><i class="status-dot absent"></i> Absent records</span><strong><?php echo $absent_today; ?></strong></div>
                <div><span>Total attendance records</span><strong><?php echo $total_attendance_records; ?></strong></div>
            </div>
        </div>
    </article>

    <article class="dashboard-v2-card attention-panel">
        <div class="card-title-row">
            <div><span class="dashboard-eyebrow dark">PRIORITY</span><h2>Needs attention</h2></div>
        </div>

        <?php if ($below_75 > 0) { ?>
        <div class="priority-item priority-warning">
            <div class="priority-icon">⚠</div>
            <div><strong>Attendance shortage</strong><p><?php echo $below_75; ?> student(s) are below the 75% attendance requirement.</p></div>
        </div>
        <?php } else { ?>
        <div class="priority-item priority-success">
            <div class="priority-icon">✓</div>
            <div><strong>Attendance is healthy</strong><p>No recorded student is currently below the 75% requirement.</p></div>
        </div>
        <?php } ?>

        <?php if ($failed_students > 0) { ?>
        <div class="priority-item priority-warning">
            <div class="priority-icon">!</div>
            <div><strong>Academic alert</strong><p><?php echo $failed_students; ?> student(s) have at least one failed subject.</p></div>
        </div>
        <?php } else { ?>
        <div class="priority-item priority-neutral">
            <div class="priority-icon">📝</div>
            <div><strong>Academic results</strong><p><?php echo $total_mark_records; ?> subject result(s) are currently published.</p></div>
        </div>
        <?php } ?>

        <a href="reports.php" class="panel-link-button">Open Academic Reports →</a>
    </article>
</section>

<section class="dashboard-focus-grid dashboard-reveal">

    <article class="dashboard-v2-card">
        <div class="card-title-row">
            <div><span class="dashboard-eyebrow dark">PERFORMANCE</span><h2>Academic snapshot</h2></div>
        </div>

        <div class="academic-snapshot">
            <div class="snapshot-stat"><span>Passed all recorded subjects</span><strong><?php echo $passed_students; ?></strong></div>
            <div class="snapshot-stat"><span>Students with failures</span><strong><?php echo $failed_students; ?></strong></div>
            <div class="snapshot-stat"><span>Published results</span><strong><?php echo $total_mark_records; ?></strong></div>
        </div>

        <?php if ($top_performer) { ?>
        <div class="v2-top-performer">
            <div class="performer-medal">🏆</div>
            <div class="performer-info">
                <small>TOP ACADEMIC PERFORMER</small>
                <strong><?php echo htmlspecialchars($top_performer["name"]); ?></strong>
                <span><?php echo htmlspecialchars($top_performer["roll_no"]); ?> • <?php echo htmlspecialchars($top_performer["department"]); ?></span>
            </div>
            <div class="performer-average"><?php echo $top_performer["average_marks"]; ?>%</div>
        </div>
        <?php } ?>
    </article>

    <article class="dashboard-v2-card">
        <div class="card-title-row">
            <div><span class="dashboard-eyebrow dark">DISTRIBUTION</span><h2>Students by department</h2></div>
        </div>

        <div class="department-v2-list">
        <?php
        if ($department_distribution && mysqli_num_rows($department_distribution) > 0) {
            while ($dept = mysqli_fetch_assoc($department_distribution)) {
                $dept_percentage = $total_students > 0
                    ? round(($dept["total"] / $total_students) * 100, 1)
                    : 0;
        ?>
            <div class="department-v2-row">
                <div><strong><?php echo htmlspecialchars($dept["department"]); ?></strong><span><?php echo intval($dept["total"]); ?> students</span></div>
                <div class="department-v2-track"><span style="width:<?php echo min(100, $dept_percentage); ?>%"></span></div>
            </div>
        <?php
            }
        } else {
        ?>
            <div class="dashboard-v2-empty">No department information available.</div>
        <?php } ?>
        </div>
    </article>
</section>

<section class="dashboard-v2-section dashboard-reveal">
    <div class="dashboard-v2-heading">
        <div><span class="dashboard-eyebrow dark">SHORTCUTS</span><h2>Quick actions</h2></div>
    </div>

    <div class="dashboard-action-grid">
        <a href="add_student.php"><span>👨‍🎓</span><div><strong>Add Student</strong><small>Create a student profile</small></div><b>→</b></a>
        <a href="attendance.php"><span>✓</span><div><strong>Take Attendance</strong><small>Start a class session</small></div><b>→</b></a>
        <a href="marks.php"><span>📝</span><div><strong>Publish Marks</strong><small>Record academic results</small></div><b>→</b></a>
        <a href="reports.php"><span>📊</span><div><strong>View Reports</strong><small>Explore academic insights</small></div><b>→</b></a>
    </div>
</section>

<section class="dashboard-v2-section dashboard-reveal">
    <div class="dashboard-v2-heading">
        <div><span class="dashboard-eyebrow dark">RECENT ACTIVITY</span><h2>Latest attendance sessions</h2></div>
        <a href="attendance_history.php" class="section-text-link">See all →</a>
    </div>

    <div class="dashboard-v2-table-card">
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Year / Semester</th>
                    <th>Subject</th>
                    <th>Period</th>
                </tr>
            </thead>
            <tbody>
            <?php
            if ($recent_sessions && mysqli_num_rows($recent_sessions) > 0) {
                while ($session = mysqli_fetch_assoc($recent_sessions)) {
            ?>
                <tr>
                    <td><strong><?php echo date("d M", strtotime($session["attendance_date"])); ?></strong><br><small><?php echo date("Y", strtotime($session["attendance_date"])); ?></small></td>
                    <td><span class="class-pill"><?php echo htmlspecialchars($session["department"]); ?> • <?php echo htmlspecialchars($session["section"]); ?></span></td>
                    <td><?php echo htmlspecialchars($session["year"]); ?><br><small>Semester <?php echo htmlspecialchars($session["semester"]); ?></small></td>
                    <td><strong><?php echo htmlspecialchars($session["subject_name"]); ?></strong><br><small><?php echo htmlspecialchars($session["subject_code"]); ?></small></td>
                    <td><span class="period-pill">P<?php echo intval($session["start_period"]); ?><?php if ($session["start_period"] != $session["end_period"]) { ?>–P<?php echo intval($session["end_period"]); ?><?php } ?></span></td>
                </tr>
            <?php
                }
            } else {
            ?>
                <tr><td colspan="5" class="dashboard-v2-empty">No attendance sessions recorded yet.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</section>

</main>
</div>

<script src="js/script.js"></script>
</body>
</html>
