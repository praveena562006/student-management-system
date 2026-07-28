<?php

$current_page = basename($_SERVER["PHP_SELF"]);

function adminNavActive($pages)
{
    global $current_page;

    return in_array($current_page, $pages)
        ? "active"
        : "";
}

?>

<div class="sidebar edutrack-sidebar">

    <div class="sidebar-brand-block">

        <div class="sidebar-brand-icon">
            🎓
        </div>

        <div>
            <strong>EduTrack</strong>
            <small>Academic Management</small>
        </div>

    </div>


    <!-- DASHBOARD -->

    <div class="sidebar-group">

        <a
            href="dashboard.php"
            class="<?php echo adminNavActive(["dashboard.php"]); ?>"
        >
            <span>🏠</span>
            Dashboard
        </a>

    </div>


    <!-- STUDENT MANAGEMENT -->

    <div class="sidebar-section-title">
        STUDENT MANAGEMENT
    </div>

    <div class="sidebar-group">

        <a
            href="view_students.php"
            class="<?php echo adminNavActive(["view_students.php", "student_profile.php", "edit_student.php"]); ?>"
        >
            <span>👨‍🎓</span>
            All Students
        </a>

        <a
            href="add_student.php"
            class="<?php echo adminNavActive(["add_student.php"]); ?>"
        >
            <span>➕</span>
            Add Student
        </a>

    </div>


    <!-- ACADEMICS -->

    <div class="sidebar-section-title">
        ACADEMICS
    </div>

    <div class="sidebar-group">

        <a
            href="attendance.php"
            class="<?php echo adminNavActive(["attendance.php"]); ?>"
        >
            <span>✓</span>
            Take Attendance
        </a>

        <a
            href="attendance_history.php"
            class="<?php echo adminNavActive(["attendance_history.php"]); ?>"
        >
            <span>📋</span>
            Attendance History
        </a>

        <a
            href="marks.php"
            class="<?php echo adminNavActive(["marks.php"]); ?>"
        >
            <span>📝</span>
            Marks & Grades
        </a>

        <a
            href="subjects.php"
            class="<?php echo adminNavActive(["subjects.php"]); ?>"
        >
            <span>📚</span>
            Subjects
        </a>

    </div>


    <!-- INSIGHTS -->

    <div class="sidebar-section-title">
        INSIGHTS
    </div>

    <div class="sidebar-group">

        <a
            href="reports.php"
            class="<?php echo adminNavActive(["reports.php"]); ?>"
        >
            <span>📊</span>
            Reports & Analytics
        </a>

    </div>


    <!-- ACCOUNT -->

    <div class="sidebar-section-title">
        ACCOUNT
    </div>

    <div class="sidebar-group sidebar-account">

        <a href="logout.php">
            <span>🚪</span>
            Logout
        </a>

    </div>

</div>