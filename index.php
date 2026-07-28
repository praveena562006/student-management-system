<?php

session_start();

/* =========================================================
   REDIRECT ALREADY LOGGED-IN USERS
========================================================= */

if (isset($_SESSION["admin"])) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_SESSION["student_id"])) {
    header("Location: student_dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<meta
    name="description"
    content="EduTrack - Smart Student Academic Management System"
>

<title>
EduTrack | Smart Academic Management
</title>

<link
    rel="stylesheet"
    href="css/style.css"
>

<style>

/* =========================================================
   EDUTRACK LANDING PAGE
========================================================= */

.edutrack-home {
    min-height: 100vh;
    background: #f7f9fc;
    color: #101828;
}


/* ================= NAVIGATION ================= */

.home-nav {
    height: 76px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 clamp(25px, 6vw, 90px);

    background: rgba(255,255,255,.94);
    border-bottom: 1px solid #e8ecf4;

    position: sticky;
    top: 0;
    z-index: 100;

    backdrop-filter: blur(14px);
}


.home-brand {
    display: flex;
    align-items: center;
    gap: 12px;
}


.home-brand-icon {
    width: 44px;
    height: 44px;

    display: grid;
    place-items: center;

    border-radius: 13px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5,
            #7c3aed
        );

    color: white;

    font-size: 22px;

    box-shadow:
        0 8px 20px
        rgba(79,70,229,.25);
}


.home-brand h2 {
    margin: 0;

    font-size: 22px;

    letter-spacing: -.5px;
}


.home-brand small {
    display: block;

    margin-top: 1px;

    color: #667085;

    font-size: 10px;

    letter-spacing: .08em;

    text-transform: uppercase;
}


.home-nav-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}


.home-nav-link {
    padding: 10px 17px;

    border-radius: 9px;

    color: #344054;

    text-decoration: none;

    font-weight: 600;

    transition: .2s;
}


.home-nav-link:hover {
    background: #f1f4f9;
}


.home-admin-button {
    padding: 11px 19px;

    border-radius: 10px;

    color: white;

    text-decoration: none;

    font-weight: 650;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #4f46e5
        );

    box-shadow:
        0 8px 18px
        rgba(37,99,235,.20);

    transition: .2s;
}


.home-admin-button:hover {
    transform: translateY(-2px);

    box-shadow:
        0 12px 25px
        rgba(37,99,235,.28);
}


/* ================= HERO ================= */

.home-hero {
    position: relative;

    overflow: hidden;

    min-height: 620px;

    display: flex;
    align-items: center;

    padding:
        70px
        clamp(25px, 6vw, 90px);

    background:
        radial-gradient(
            circle at 85% 15%,
            rgba(124,58,237,.22),
            transparent 27rem
        ),
        radial-gradient(
            circle at 10% 90%,
            rgba(37,99,235,.18),
            transparent 30rem
        ),
        linear-gradient(
            135deg,
            #07111f,
            #0c1d38 48%,
            #18285c
        );

    color: white;
}


.home-hero::before {
    content: "";

    position: absolute;

    width: 500px;
    height: 500px;

    border: 1px solid
        rgba(255,255,255,.06);

    border-radius: 50%;

    right: -190px;
    top: -200px;
}


.home-hero-grid {
    width: 100%;
    max-width: 1400px;

    margin: auto;

    display: grid;

    grid-template-columns:
        1.08fr .92fr;

    gap: clamp(45px, 7vw, 100px);

    align-items: center;

    position: relative;

    z-index: 2;
}


.hero-badge {
    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 8px 13px;

    border-radius: 30px;

    border:
        1px solid
        rgba(255,255,255,.14);

    background:
        rgba(255,255,255,.07);

    color: #dbeafe;

    font-size: 12px;

    font-weight: 650;

    letter-spacing: .06em;

    text-transform: uppercase;
}


.hero-copy h1 {
    margin:
        24px
        0
        20px;

    max-width: 760px;

    font-size:
        clamp(45px, 6vw, 76px);

    line-height: .99;

    letter-spacing: -3px;

    font-weight: 800;
}


.hero-gradient-text {
    background:
        linear-gradient(
            90deg,
            #60a5fa,
            #a5b4fc,
            #c4b5fd
        );

    -webkit-background-clip: text;

    color: transparent;
}


.hero-copy > p {
    max-width: 650px;

    color: #b9c6d9;

    font-size: 18px;

    line-height: 1.75;
}


.hero-actions {
    display: flex;

    flex-wrap: wrap;

    gap: 13px;

    margin-top: 30px;
}


.hero-primary {
    padding: 14px 23px;

    border-radius: 11px;

    background:
        linear-gradient(
            135deg,
            #2563eb,
            #6366f1
        );

    color: white;

    text-decoration: none;

    font-weight: 700;

    box-shadow:
        0 12px 25px
        rgba(37,99,235,.30);
}


.hero-secondary {
    padding: 14px 23px;

    border-radius: 11px;

    border:
        1px solid
        rgba(255,255,255,.17);

    background:
        rgba(255,255,255,.07);

    color: white;

    text-decoration: none;

    font-weight: 650;
}


.hero-primary,
.hero-secondary {
    transition: .2s;
}


.hero-primary:hover,
.hero-secondary:hover {
    transform: translateY(-3px);
}


/* ================= HERO VISUAL ================= */

.hero-dashboard-preview {
    padding: 18px;

    border:
        1px solid
        rgba(255,255,255,.14);

    border-radius: 24px;

    background:
        rgba(255,255,255,.08);

    box-shadow:
        0 35px 80px
        rgba(0,0,0,.30);

    backdrop-filter: blur(14px);

    transform:
        perspective(1100px)
        rotateY(-4deg)
        rotateX(2deg);
}


.preview-window {
    overflow: hidden;

    border-radius: 16px;

    background: #f7f9fc;

    color: #101828;
}


.preview-bar {
    height: 46px;

    display: flex;

    align-items: center;

    gap: 7px;

    padding: 0 16px;

    background: white;

    border-bottom:
        1px solid #e8ecf3;
}


.preview-dot {
    width: 8px;
    height: 8px;

    border-radius: 50%;

    background: #d0d5dd;
}


.preview-body {
    display: grid;

    grid-template-columns:
        105px 1fr;

    min-height: 390px;
}


.preview-sidebar {
    padding: 20px 10px;

    background: #0b1220;
}


.preview-logo {
    width: 35px;
    height: 35px;

    margin:
        0 auto
        25px;

    display: grid;

    place-items: center;

    border-radius: 9px;

    background: #4f46e5;
}


.preview-menu {
    height: 8px;

    margin: 13px 5px;

    border-radius: 5px;

    background:
        rgba(255,255,255,.14);
}


.preview-menu.active {
    height: 30px;

    background:
        linear-gradient(
            90deg,
            #2563eb,
            #4f46e5
        );
}


.preview-content {
    padding: 23px;
}


.preview-heading {
    width: 43%;
    height: 17px;

    border-radius: 6px;

    background: #172033;

    margin-bottom: 22px;
}


.preview-stats {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 10px;
}


.preview-stat {
    height: 75px;

    border:
        1px solid #e6eaf1;

    border-radius: 11px;

    background: white;

    padding: 13px;
}


.preview-stat::before {
    content: "";

    display: block;

    width: 45%;
    height: 7px;

    margin-bottom: 13px;

    border-radius: 4px;

    background: #d8deea;
}


.preview-stat::after {
    content: "";

    display: block;

    width: 28%;
    height: 17px;

    border-radius: 5px;

    background: #4f46e5;
}


.preview-chart {
    height: 145px;

    margin-top: 15px;

    border:
        1px solid #e6eaf1;

    border-radius: 12px;

    background:
        linear-gradient(
            180deg,
            #fff,
            #f8faff
        );

    position: relative;

    overflow: hidden;
}


.chart-bars {
    height: 100%;

    display: flex;

    align-items: flex-end;

    gap: 14px;

    padding:
        28px
        25px
        15px;
}


.chart-bar {
    flex: 1;

    border-radius:
        5px 5px 2px 2px;

    background:
        linear-gradient(
            #6366f1,
            #2563eb
        );
}


.chart-bar:nth-child(1) {
    height: 45%;
}

.chart-bar:nth-child(2) {
    height: 72%;
}

.chart-bar:nth-child(3) {
    height: 58%;
}

.chart-bar:nth-child(4) {
    height: 86%;
}

.chart-bar:nth-child(5) {
    height: 68%;
}

.chart-bar:nth-child(6) {
    height: 91%;
}


/* ================= HERO TRUST STRIP ================= */

.hero-trust {
    display: flex;

    flex-wrap: wrap;

    gap: 22px;

    margin-top: 35px;

    color: #9fb0c7;

    font-size: 13px;
}


.hero-trust span {
    display: flex;

    align-items: center;

    gap: 7px;
}


/* ================= FEATURE SECTION ================= */

.home-section {
    padding:
        90px
        clamp(25px, 6vw, 90px);
}


.section-heading {
    max-width: 720px;

    margin:
        0 auto
        55px;

    text-align: center;
}


.section-kicker {
    color: #4f46e5;

    font-size: 12px;

    font-weight: 750;

    letter-spacing: .12em;

    text-transform: uppercase;
}


.section-heading h2 {
    margin:
        12px 0
        14px;

    font-size:
        clamp(31px, 4vw, 44px);

    letter-spacing: -1.4px;
}


.section-heading p {
    color: #667085;

    line-height: 1.7;

    font-size: 16px;
}


.feature-grid {
    max-width: 1250px;

    margin: auto;

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;
}


.feature-card {
    padding: 28px;

    border:
        1px solid #e5eaf2;

    border-radius: 17px;

    background: white;

    box-shadow:
        0 10px 30px
        rgba(15,23,42,.05);

    transition: .25s;
}


.feature-card:hover {
    transform:
        translateY(-6px);

    border-color: #c7d2fe;

    box-shadow:
        0 22px 45px
        rgba(15,23,42,.10);
}


.feature-icon {
    width: 50px;
    height: 50px;

    display: grid;

    place-items: center;

    border-radius: 13px;

    margin-bottom: 20px;

    background: #eef2ff;

    font-size: 23px;
}


.feature-card h3 {
    margin:
        0 0
        10px;

    font-size: 18px;
}


.feature-card p {
    margin: 0;

    color: #667085;

    line-height: 1.65;

    font-size: 14px;
}


/* ================= ACADEMIC INTELLIGENCE ================= */

.intelligence-section {
    padding:
        85px
        clamp(25px, 6vw, 90px);

    background:
        linear-gradient(
            135deg,
            #eef4ff,
            #f8f7ff
        );
}


.intelligence-grid {
    max-width: 1250px;

    margin: auto;

    display: grid;

    grid-template-columns:
        .9fr 1.1fr;

    gap: 60px;

    align-items: center;
}


.intelligence-copy h2 {
    font-size:
        clamp(31px, 4vw, 44px);

    line-height: 1.1;

    letter-spacing: -1.3px;
}


.intelligence-copy p {
    color: #667085;

    line-height: 1.75;
}


.intelligence-list {
    display: grid;

    gap: 12px;

    margin-top: 25px;
}


.intelligence-item {
    display: flex;

    gap: 12px;

    align-items: center;

    padding: 14px;

    border-radius: 11px;

    background:
        rgba(255,255,255,.72);

    border:
        1px solid
        rgba(255,255,255,.8);
}


.intelligence-check {
    width: 31px;
    height: 31px;

    display: grid;

    place-items: center;

    border-radius: 50%;

    background: #dcfce7;

    color: #15803d;

    font-weight: bold;
}


.analytics-card {
    padding: 25px;

    border-radius: 20px;

    background: white;

    box-shadow:
        0 25px 60px
        rgba(51,65,85,.12);
}


.analytics-top {
    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-bottom: 20px;
}


.analytics-top span {
    color: #667085;

    font-size: 12px;
}


.risk-list {
    display: grid;

    gap: 10px;
}


.risk-row {
    display: grid;

    grid-template-columns:
        1fr auto auto;

    gap: 15px;

    align-items: center;

    padding: 14px;

    border:
        1px solid #edf0f5;

    border-radius: 11px;
}


.risk-student strong {
    display: block;
}


.risk-student small {
    color: #98a2b3;
}


.risk-percentage {
    font-weight: 750;
}


.risk-warning {
    padding: 6px 9px;

    border-radius: 20px;

    background: #fff7ed;

    color: #c2410c;

    font-size: 11px;

    font-weight: 700;
}


.risk-good {
    padding: 6px 9px;

    border-radius: 20px;

    background: #ecfdf3;

    color: #15803d;

    font-size: 11px;

    font-weight: 700;
}


/* ================= PORTALS ================= */

.access-section {
    padding:
        90px
        clamp(25px, 6vw, 90px);
}


.access-grid {
    max-width: 1050px;

    margin: auto;

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 22px;
}


.access-card {
    position: relative;

    overflow: hidden;

    padding: 34px;

    border-radius: 20px;

    color: white;

    text-decoration: none;

    min-height: 270px;

    transition: .25s;
}


.access-card:hover {
    transform:
        translateY(-7px);
}


.access-admin {
    background:
        linear-gradient(
            135deg,
            #172554,
            #1d4ed8
        );
}


.access-student {
    background:
        linear-gradient(
            135deg,
            #312e81,
            #6d28d9
        );
}


.access-card::after {
    content: "";

    position: absolute;

    width: 190px;
    height: 190px;

    border-radius: 50%;

    right: -55px;
    bottom: -65px;

    background:
        rgba(255,255,255,.09);
}


.access-icon {
    font-size: 38px;
}


.access-card h3 {
    margin:
        22px 0
        10px;

    font-size: 26px;
}


.access-card p {
    max-width: 370px;

    color:
        rgba(255,255,255,.74);

    line-height: 1.6;
}


.access-arrow {
    display: inline-block;

    margin-top: 17px;

    font-weight: 700;
}


/* ================= FOOTER ================= */

.home-footer {
    padding:
        35px
        clamp(25px, 6vw, 90px);

    background: #08111f;

    color: #94a3b8;
}


.footer-inner {
    max-width: 1250px;

    margin: auto;

    display: flex;

    justify-content: space-between;

    gap: 25px;

    align-items: center;
}


.footer-brand {
    color: white;

    font-weight: 750;

    font-size: 18px;
}


.footer-right {
    text-align: right;

    font-size: 12px;
}


/* ================= RESPONSIVE ================= */

@media (max-width: 1000px) {

    .home-hero-grid,
    .intelligence-grid {
        grid-template-columns: 1fr;
    }

    .hero-dashboard-preview {
        max-width: 700px;

        transform: none;
    }

    .feature-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 700px) {

    .home-nav {
        height: auto;

        padding: 12px 18px;
    }

    .home-brand small {
        display: none;
    }

    .home-nav-link {
        display: none;
    }

    .home-hero {
        padding:
            55px 20px;
    }

    .hero-copy h1 {
        letter-spacing: -2px;
    }

    .hero-copy > p {
        font-size: 16px;
    }

    .preview-body {
        grid-template-columns:
            70px 1fr;
    }

    .preview-content {
        padding: 15px;
    }

    .preview-stats {
        grid-template-columns: 1fr;
    }

    .preview-stat:nth-child(3) {
        display: none;
    }

    .feature-grid,
    .access-grid {
        grid-template-columns: 1fr;
    }

    .home-section,
    .intelligence-section,
    .access-section {
        padding:
            65px 20px;
    }

    .footer-inner {
        flex-direction: column;

        text-align: center;
    }

    .footer-right {
        text-align: center;
    }
}

</style>

</head>


<body>

<div class="edutrack-home">


<!-- =====================================================
     NAVIGATION
===================================================== -->

<nav class="home-nav">

    <div class="home-brand">

        <div class="home-brand-icon">
            🎓
        </div>

        <div>

            <h2>
                EduTrack
            </h2>

            <small>
                Academic Management System
            </small>

        </div>

    </div>


    <div class="home-nav-actions">

        <a
            href="#features"
            class="home-nav-link"
        >
            Features
        </a>

        <a
            href="student_login.php"
            class="home-nav-link"
        >
            Student Portal
        </a>

        <a
            href="login.php"
            class="home-admin-button"
        >
            Admin Login
        </a>

    </div>

</nav>



<!-- =====================================================
     HERO
===================================================== -->

<section class="home-hero">


<div class="home-hero-grid">


<!-- LEFT -->

<div class="hero-copy">


    <div class="hero-badge">

        ● Smart Academic Platform

    </div>


    <h1>

        Manage academics.

        <span class="hero-gradient-text">

            Understand performance.

        </span>

    </h1>


    <p>

        EduTrack brings student records,
        period-wise attendance, academic results,
        timetable management and performance
        insights together in one secure platform.

    </p>


    <div class="hero-actions">


        <a
            href="student_login.php"
            class="hero-primary"
        >

            Student Portal →

        </a>


        <a
            href="login.php"
            class="hero-secondary"
        >

            Administrator Access

        </a>


    </div>


    <div class="hero-trust">

        <span>
            ✓ Role-Based Access
        </span>

        <span>
            ✓ Email Verification
        </span>

        <span>
            ✓ Academic Analytics
        </span>

    </div>


</div>



<!-- RIGHT VISUAL -->

<div class="hero-dashboard-preview">


<div class="preview-window">


<div class="preview-bar">

    <span class="preview-dot"></span>
    <span class="preview-dot"></span>
    <span class="preview-dot"></span>

</div>


<div class="preview-body">


<div class="preview-sidebar">

    <div class="preview-logo">
        🎓
    </div>

    <div class="preview-menu active"></div>

    <div class="preview-menu"></div>

    <div class="preview-menu"></div>

    <div class="preview-menu"></div>

    <div class="preview-menu"></div>

</div>


<div class="preview-content">


<div class="preview-heading"></div>


<div class="preview-stats">

    <div class="preview-stat"></div>

    <div class="preview-stat"></div>

    <div class="preview-stat"></div>

</div>


<div class="preview-chart">

<div class="chart-bars">

    <div class="chart-bar"></div>

    <div class="chart-bar"></div>

    <div class="chart-bar"></div>

    <div class="chart-bar"></div>

    <div class="chart-bar"></div>

    <div class="chart-bar"></div>

</div>

</div>


</div>


</div>


</div>

</div>


</div>

</section>



<!-- =====================================================
     FEATURES
===================================================== -->

<section
    class="home-section"
    id="features"
>


<div class="section-heading">


<div class="section-kicker">

Academic Operations

</div>


<h2>

Everything academics need,
in one platform.

</h2>


<p>

Designed to simplify academic administration
while giving students clear access to their
attendance and academic performance.

</p>


</div>



<div class="feature-grid">


<div class="feature-card">

<div class="feature-icon">
📅
</div>

<h3>
Smart Attendance
</h3>

<p>

Record attendance by subject,
period, department and section
using the academic timetable.

</p>

</div>



<div class="feature-card">

<div class="feature-icon">
📚
</div>

<h3>
Student Management
</h3>

<p>

Maintain student profiles,
academic information, departments,
sections and login accounts.

</p>

</div>



<div class="feature-card">

<div class="feature-icon">
📝
</div>

<h3>
Marks & Grades
</h3>

<p>

Record internal and external marks
with automatic total, grade and
result calculation.

</p>

</div>



<div class="feature-card">

<div class="feature-icon">
🗓️
</div>

<h3>
Timetable Integration
</h3>

<p>

Connect attendance directly to
department and section-wise
academic timetables.

</p>

</div>



<div class="feature-card">

<div class="feature-icon">
📊
</div>

<h3>
Academic Analytics
</h3>

<p>

Understand attendance trends,
student results and academic
performance through reports.

</p>

</div>



<div class="feature-card">

<div class="feature-icon">
🔐
</div>

<h3>
Secure Student Access
</h3>

<p>

Separate student authentication,
email verification and role-based
access to academic records.

</p>

</div>


</div>

</section>



<!-- =====================================================
     ACADEMIC INTELLIGENCE
===================================================== -->

<section class="intelligence-section">


<div class="intelligence-grid">


<div class="intelligence-copy">


<div class="section-kicker">

Beyond Record Keeping

</div>


<h2>

Turn academic records into
useful insights.

</h2>


<p>

EduTrack is designed not only to store
student information, but also to help
academic administrators identify attendance
shortages and understand student performance.

</p>


<div class="intelligence-list">


<div class="intelligence-item">

<div class="intelligence-check">
✓
</div>

<div>

<strong>
Period-wise attendance tracking
</strong>

</div>

</div>


<div class="intelligence-item">

<div class="intelligence-check">
✓
</div>

<div>

<strong>
75% attendance monitoring
</strong>

</div>

</div>


<div class="intelligence-item">

<div class="intelligence-check">
✓
</div>

<div>

<strong>
Subject-wise academic results
</strong>

</div>

</div>


<div class="intelligence-item">

<div class="intelligence-check">
✓
</div>

<div>

<strong>
Department and section analytics
</strong>

</div>

</div>


</div>


</div>



<!-- DEMO ANALYTICS CARD -->

<div class="analytics-card">


<div class="analytics-top">

<div>

<strong>
Academic Overview
</strong>

<br>

<span>
Illustrative dashboard preview
</span>

</div>

<span>
EduTrack Analytics
</span>

</div>


<div class="risk-list">


<div class="risk-row">

<div class="risk-student">

<strong>
Attendance Monitoring
</strong>

<small>
Minimum academic requirement
</small>

</div>

<div class="risk-percentage">
75%
</div>

<div class="risk-good">
TRACKED
</div>

</div>



<div class="risk-row">

<div class="risk-student">

<strong>
Period-wise Sessions
</strong>

<small>
Timetable connected
</small>

</div>

<div class="risk-percentage">
P1–P7
</div>

<div class="risk-good">
ACTIVE
</div>

</div>



<div class="risk-row">

<div class="risk-student">

<strong>
Low Attendance
</strong>

<small>
Academic alert identification
</small>

</div>

<div class="risk-percentage">
&lt;75%
</div>

<div class="risk-warning">
ATTENTION
</div>

</div>



<div class="risk-row">

<div class="risk-student">

<strong>
Academic Results
</strong>

<small>
Subject-wise performance
</small>

</div>

<div class="risk-percentage">
100
</div>

<div class="risk-good">
ANALYZED
</div>

</div>


</div>


</div>


</div>

</section>



<!-- =====================================================
     PORTAL ACCESS
===================================================== -->

<section class="access-section">


<div class="section-heading">


<div class="section-kicker">

Secure Access

</div>


<h2>

One platform.
Two focused experiences.

</h2>


<p>

Administrators manage academic operations,
while students securely access their
individual academic records.

</p>


</div>



<div class="access-grid">


<a
    href="login.php"
    class="access-card access-admin"
>


<div class="access-icon">
👨‍💼
</div>


<h3>
Administrator Portal
</h3>


<p>

Manage students, timetable,
period-wise attendance, marks,
subjects and academic reports.

</p>


<span class="access-arrow">

Open Admin Portal →

</span>


</a>



<a
    href="student_login.php"
    class="access-card access-student"
>


<div class="access-icon">
🎓
</div>


<h3>
Student Portal
</h3>


<p>

View personal attendance,
academic results, grades and
overall performance securely.

</p>


<span class="access-arrow">

Open Student Portal →

</span>


</a>


</div>

</section>



<!-- =====================================================
     FOOTER
===================================================== -->

<footer class="home-footer">


<div class="footer-inner">


<div>

<div class="footer-brand">

🎓 EduTrack

</div>

<small>

Smart Student Academic Management System

</small>

</div>


<div class="footer-right">

Academic Management • Attendance • Performance

<br>

Secure Role-Based Platform

</div>


</div>


</footer>


</div>

</body>

</html>