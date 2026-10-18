
<?php
$pageTitle = "MediBook+ | Your Health, Our Priority";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover doctors, explore medical departments and manage hospital appointments with MediBook.">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1765e9;
            --primary-dark: #104bb5;
            --navy: #14284b;
            --muted: #64748b;
            --background: #f5f8ff;
            --border: #e6edfa;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 90px;
        }

        body {
            margin: 0;
            font-family: 'DM Sans', sans-serif;
            color: var(--navy);
            background: var(--white);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid #edf1f8;
            padding: 15px 0;
        }

        .navbar-brand {
            font-size: 25px;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -1px;
        }

        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            margin-right: 7px;
            border-radius: 12px;
            background: var(--primary);
            color: white;
            font-size: 25px;
            vertical-align: middle;
        }

        .navbar .nav-link {
            color: #435574;
            font-size: 14px;
            font-weight: 600;
            margin: 0 9px;
        }

        .navbar .nav-link:hover {
            color: var(--primary);
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: var(--primary);
            border: 1px solid var(--primary);
            color: white;
            border-radius: 10px;
            padding: 12px 20px;
            font-weight: 700;
            font-size: 14px;
            transition: 0.2s ease;
        }

        .btn-primary-custom:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: white;
            transform: translateY(-1px);
        }

        .btn-outline-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid #d6e2f5;
            color: var(--navy);
            border-radius: 10px;
            padding: 11px 19px;
            font-size: 14px;
            font-weight: 700;
        }

        .btn-outline-custom:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Hero */

        .hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(ellipse at 85% 25%, #dceaff 0%, transparent 40%),
                linear-gradient(120deg, #f5f8ff 0%, #eef5ff 100%);
            padding: 75px 0 90px;
        }

        .hero::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            right: -170px;
            top: -180px;
            border-radius: 50%;
            border: 1px solid rgba(23, 101, 233, 0.1);
            box-shadow: 0 0 0 45px rgba(23, 101, 233, 0.025),
                        0 0 0 90px rgba(23, 101, 233, 0.02);
        }

        .hero .container {
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: var(--primary);
            background: #e6efff;
            border: 1px solid #d8e6ff;
            border-radius: 30px;
            padding: 7px 13px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 23px 0 19px;
            color: var(--navy);
            font-size: clamp(38px, 5vw, 62px);
            font-weight: 800;
            letter-spacing: -2.6px;
            line-height: 1.1;
        }

        .hero h1 span {
            color: var(--primary);
        }

        .hero-description {
            max-width: 490px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.85;
            margin-bottom: 27px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .hero-visual {
            position: relative;
            min-height: 430px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-image-frame {
            position: relative;
            width: 100%;
            max-width: 480px;
            height: 410px;
            overflow: hidden;
            border-radius: 170px 170px 28px 28px;
            background: #d8e8ff;
            box-shadow: 0 25px 65px rgba(30, 78, 150, 0.15);
        }

        .hero-image-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 25%;
        }

        .floating-card {
            position: absolute;
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 14px 17px;
            border: 1px solid #edf1f8;
            border-radius: 15px;
            box-shadow: 0 12px 35px rgba(22, 53, 100, 0.12);
        }

        .floating-card.card-one {
            left: -25px;
            bottom: 43px;
        }

        .floating-card.card-two {
            right: -12px;
            top: 47px;
        }

        .floating-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 43px;
            height: 43px;
            border-radius: 12px;
            background: #eaf2ff;
            color: var(--primary);
            font-size: 22px;
        }

        .floating-card strong {
            display: block;
            font-size: 13px;
        }

        .floating-card small {
            display: block;
            color: var(--muted);
            font-size: 11px;
            margin-top: 2px;
        }

        /* Search */

        .search-panel {
            position: relative;
            z-index: 2;
            margin-top: -42px;
        }

        .search-box {
            padding: 24px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 15px 45px rgba(34, 72, 130, 0.10);
        }

        .search-heading {
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .search-subtitle {
            color: var(--muted);
            font-size: 13px;
        }

        .form-control,
        .form-select {
            min-height: 49px;
            border: 1px solid #dce5f2;
            border-radius: 10px;
            color: var(--navy);
            font-size: 14px;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
        }

        /* Trust features */

        .trust-section {
            padding: 55px 0 30px;
        }

        .trust-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 13px;
            padding: 15px 10px;
        }

        .trust-icon {
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: #edf4ff;
            color: var(--primary);
            border-radius: 14px;
            font-size: 23px;
        }

        .trust-item strong {
            display: block;
            font-size: 13px;
        }

        .trust-item small {
            display: block;
            color: var(--muted);
            font-size: 12px;
        }

        /* Sections */

        .section {
            padding: 75px 0;
        }

        .section-soft {
            background: var(--background);
        }

        .section-heading {
            text-align: center;
            max-width: 620px;
            margin: 0 auto 38px;
        }

        .section-label {
            color: var(--primary);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .section-heading h2 {
            margin: 12px 0;
            font-size: clamp(27px, 3vw, 38px);
            font-weight: 800;
            letter-spacing: -1.2px;
        }

        .section-heading p {
            color: var(--muted);
            font-size: 14px;
            margin: 0;
        }

        /* Departments */

        .department-card {
            display: block;
            height: 100%;
            padding: 25px 18px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 17px;
            text-align: center;
            transition: 0.22s ease;
        }

        .department-card:hover {
            transform: translateY(-5px);
            border-color: #c4d8ff;
            box-shadow: 0 15px 35px rgba(32, 83, 160, 0.09);
        }

        .department-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            margin: 0 auto 17px;
            border-radius: 18px;
            background: #eaf2ff;
            font-size: 28px;
        }

        .department-card h3 {
            color: var(--navy);
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .department-card p {
            color: var(--muted);
            font-size: 12px;
            margin: 0;
        }

        .department-card .department-arrow {
            display: inline-block;
            color: var(--primary);
            font-size: 13px;
            margin-top: 15px;
            font-weight: 700;
        }

        /* How it works */

        .step-card {
            padding: 28px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 17px;
            height: 100%;
        }

        .step-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 13px;
            background: #eaf2ff;
            color: var(--primary);
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .step-card h3 {
            font-size: 17px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .step-card p {
            color: var(--muted);
            font-size: 13px;
            margin: 0;
        }

        /* CTA */

        .cta {
            padding: 20px 0 75px;
        }

        .cta-panel {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
            padding: 42px;
            border-radius: 23px;
            background: linear-gradient(120deg, #e9f2ff, #d8e8ff);
        }

        .cta-panel h2 {
            font-size: clamp(23px, 3vw, 31px);
            font-weight: 800;
            margin-bottom: 9px;
        }

        .cta-panel p {
            color: #516785;
            font-size: 14px;
            margin: 0;
        }

        /* Footer */

        footer {
            background: #10213e;
            color: #c9d5e8;
            padding: 55px 0 22px;
        }

        .footer-brand {
            color: white;
            font-size: 23px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .footer-description {
            max-width: 280px;
            color: #aebdd3;
            font-size: 13px;
        }

        .footer-heading {
            color: white;
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 17px;
        }

        .footer-links {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .footer-links a {
            color: #c9d5e8;
            font-size: 13px;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.13);
            text-align: center;
            color: #aebdd3;
            font-size: 12px;
        }

        @media (max-width: 991px) {
            .hero {
                padding: 55px 0 75px;
            }

            .hero-visual {
                margin-top: 35px;
            }

            .hero-image-frame {
                max-width: 430px;
                height: 370px;
            }

            .floating-card.card-one {
                left: 0;
            }

            .floating-card.card-two {
                right: 0;
            }
        }

        @media (max-width: 575px) {
            .hero h1 {
                letter-spacing: -1.5px;
            }

            .hero-image-frame {
                height: 320px;
                border-radius: 120px 120px 23px 23px;
            }

            .hero-visual {
                min-height: 345px;
            }

            .floating-card {
                padding: 10px;
                gap: 8px;
            }

            .floating-icon {
                width: 35px;
                height: 35px;
                font-size: 18px;
            }

            .floating-card strong {
                font-size: 11px;
            }

            .floating-card small {
                font-size: 10px;
            }

            .search-box {
                padding: 18px;
            }

            .section {
                padding: 55px 0;
            }

            .cta-panel {
                align-items: flex-start;
                flex-direction: column;
                padding: 28px;
            }

            .trust-item {
                justify-content: flex-start;
            }
        }

        /* ===== MediBook spacious layout refinements ===== */
        @media (min-width: 1200px) {
            .container {
                max-width: 1360px;
            }
        }

        .navbar {
            padding: 19px 0;
        }

        .navbar-brand {
            font-size: 29px;
        }

        .navbar .nav-link {
            font-size: 15px;
            margin: 0 12px;
        }

        .hero {
            padding: 95px 0 115px;
        }

        .hero h1 {
            font-size: clamp(46px, 4.5vw, 70px);
            line-height: 1.13;
            margin: 28px 0 24px;
        }

        .hero-description {
            max-width: 540px;
            font-size: 17px;
            line-height: 1.9;
            margin-bottom: 32px;
        }

        .hero-actions {
            gap: 15px;
        }

        .btn-primary-custom,
        .btn-outline-custom {
            padding: 14px 22px;
            font-size: 15px;
        }

        .hero-visual {
            min-height: 490px;
        }

        .hero-image-frame {
            max-width: 510px;
            height: 465px;
        }

        .floating-card {
            padding: 17px 20px;
            gap: 14px;
        }

        .floating-icon {
            width: 49px;
            height: 49px;
            font-size: 24px;
        }

        .floating-card strong {
            font-size: 14px;
        }

        .floating-card small {
            font-size: 12px;
        }

        .search-panel {
            margin-top: -48px;
        }

        .search-box {
            padding: 32px;
            border-radius: 22px;
        }

        .search-heading {
            font-size: 19px;
            margin-bottom: 8px;
        }

        .search-subtitle {
            font-size: 14px;
        }

        .form-control,
        .form-select {
            min-height: 56px;
            font-size: 15px;
        }

        .form-label {
            margin-bottom: 9px;
            font-size: 13px;
        }

        .trust-section {
            padding: 65px 0 38px;
        }

        .trust-item {
            gap: 15px;
            padding: 18px 12px;
        }

        .trust-icon {
            width: 56px;
            height: 56px;
            font-size: 26px;
        }

        .trust-item strong {
            font-size: 14px;
        }

        .trust-item small {
            font-size: 13px;
        }

        .section {
            padding: 95px 0;
        }

        .section-heading {
            max-width: 700px;
            margin-bottom: 48px;
        }

        .section-heading h2 {
            font-size: clamp(32px, 3vw, 43px);
            margin: 16px 0;
        }

        .section-heading p {
            font-size: 16px;
            line-height: 1.8;
        }

        .department-card {
            padding: 32px 22px;
            border-radius: 20px;
        }

        .department-icon {
            width: 68px;
            height: 68px;
            margin-bottom: 20px;
            font-size: 31px;
        }

        .department-card h3 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .department-card p {
            font-size: 13px;
        }

        .department-card .department-arrow {
            margin-top: 20px;
            font-size: 14px;
        }

        .step-card {
            padding: 34px;
            border-radius: 20px;
        }

        .step-number {
            width: 52px;
            height: 52px;
            margin-bottom: 25px;
            font-size: 19px;
        }

        .step-card h3 {
            font-size: 19px;
            margin-bottom: 14px;
        }

        .step-card p {
            font-size: 15px;
            line-height: 1.85;
        }

        .cta {
            padding: 25px 0 95px;
        }

        .cta-panel {
            padding: 55px;
            border-radius: 26px;
            gap: 32px;
        }

        .cta-panel h2 {
            font-size: clamp(28px, 3vw, 36px);
        }

        .cta-panel p {
            font-size: 16px;
            line-height: 1.8;
        }

        footer {
            padding: 70px 0 28px;
        }

        .footer-brand {
            font-size: 26px;
        }

        .footer-description,
        .footer-links a {
            font-size: 14px;
            line-height: 1.9;
        }

        .footer-heading {
            font-size: 15px;
            margin-bottom: 20px;
        }

        .footer-bottom {
            margin-top: 48px;
            padding-top: 24px;
            font-size: 13px;
            line-height: 1.9;
        }

        @media (max-width: 1199px) {
            .hero {
                padding: 75px 0 95px;
            }

            .hero-visual {
                min-height: 440px;
            }

            .hero-image-frame {
                max-width: 450px;
                height: 420px;
            }

            .section {
                padding: 80px 0;
            }
        }

        @media (max-width: 991px) {
            .hero {
                padding: 65px 0 85px;
            }

            .hero h1 {
                font-size: clamp(40px, 6vw, 58px);
            }

            .hero-visual {
                min-height: 420px;
                margin-top: 25px;
            }

            .hero-image-frame {
                max-width: 430px;
                height: 390px;
            }

            .search-panel {
                margin-top: -35px;
            }

            .search-box {
                padding: 28px;
            }

            .section {
                padding: 70px 0;
            }

            .cta-panel {
                padding: 38px;
            }
        }

        @media (max-width: 575px) {
            .navbar {
                padding: 12px 0;
            }

            .navbar-brand {
                font-size: 25px;
            }

            .hero {
                padding: 48px 0 65px;
            }

            .hero h1 {
                font-size: 42px;
                letter-spacing: -1.5px;
                margin: 21px 0 18px;
            }

            .hero-description {
                font-size: 15px;
                line-height: 1.8;
            }

            .hero-visual {
                min-height: 350px;
                margin-top: 15px;
            }

            .hero-image-frame {
                max-width: 100%;
                height: 325px;
                border-radius: 120px 120px 23px 23px;
            }

            .floating-card {
                padding: 10px 12px;
                gap: 8px;
            }

            .floating-icon {
                width: 36px;
                height: 36px;
                font-size: 18px;
            }

            .floating-card strong {
                font-size: 11px;
            }

            .floating-card small {
                font-size: 10px;
            }

            .search-panel {
                margin-top: -25px;
            }

            .search-box {
                padding: 22px;
            }

            .trust-section {
                padding: 38px 0 25px;
            }

            .section {
                padding: 58px 0;
            }

            .section-heading {
                margin-bottom: 30px;
            }

            .section-heading h2 {
                font-size: 31px;
            }

            .department-card {
                padding: 24px 12px;
            }

            .step-card {
                padding: 25px;
            }

            .cta {
                padding: 10px 0 60px;
            }

            .cta-panel {
                padding: 28px;
                align-items: flex-start;
                flex-direction: column;
            }

            footer {
                padding: 48px 0 22px;
            }
        }

    </style>
</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#home">
            <span class="brand-icon">+</span>MediBook<span style="color:#1765e9">.</span>
        </a>

        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNavigation"
                aria-controls="mainNavigation" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavigation">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#departments">Departments</a></li>
                <li class="nav-item"><a class="nav-link" href="#how-it-works">How It Works</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                <li class="nav-item ms-lg-2 mt-3 mt-lg-0">
                    <a class="btn-outline-custom w-100" href="#appointment">Book Appointment</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>

<!-- Hero -->
<section class="hero" id="home">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span class="eyebrow">♥ &nbsp; Your health, our priority</span>

                <h1>
                    Healthcare Made<br>
                    <span>Simple &amp; Smart.</span>
                </h1>

                <p class="hero-description">
                    Find the right doctors, explore medical departments,
                    and manage your hospital appointments — all in one place.
                </p>

                <div class="hero-actions">
                    <a href="#appointment" class="btn-primary-custom">
                        Book an Appointment <span aria-hidden="true">→</span>
                    </a>
                    <a href="#departments" class="btn-outline-custom">
                        Explore Departments
                    </a>
                </div>

                <div class="d-flex flex-wrap gap-4 mt-4">
                    <div class="small fw-semibold">
                        <span style="color:#1765e9">✓</span> Easy booking
                    </div>
                    <div class="small fw-semibold">
                        <span style="color:#1765e9">✓</span> Trusted care
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-visual">
                    <div class="hero-image-frame">
                        <img
                            src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=1000&q=85"
                            alt="Doctor ready to provide healthcare"
                        >
                    </div>

                    <div class="floating-card card-one">
                        <div class="floating-icon">🩺</div>
                        <div>
                            <strong>Professional Care</strong>
                            <small>Care that puts you first</small>
                        </div>
                    </div>

                    <div class="floating-card card-two">
                        <div class="floating-icon">📅</div>
                        <div>
                            <strong>Easy Appointments</strong>
                            <small>Plan your visit simply</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Appointment Search -->
<section class="search-panel" id="appointment">
    <div class="container">
        <div class="search-box">
            <div class="row align-items-center g-3">
                <div class="col-lg-3">
                    <div class="search-heading">Find the right doctor</div>
                    <div class="search-subtitle">Start your healthcare journey</div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="departmentSearch" class="form-label small fw-semibold">
                        Medical department
                    </label>
                    <select class="form-select" id="departmentSearch">
                        <option value="">All departments</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Neurology">Neurology</option>
                        <option value="Orthopedics">Orthopedics</option>
                        <option value="Pediatrics">Pediatrics</option>
                        <option value="Dermatology">Dermatology</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label for="doctorSearch" class="form-label small fw-semibold">
                        Doctor's name
                    </label>
                    <input type="text" class="form-control" id="doctorSearch"
                           placeholder="Enter doctor's name">
                </div>

                <div class="col-lg-3">
                    <label class="form-label d-none d-lg-block">&nbsp;</label>
                    <button type="button" class="btn-primary-custom w-100"
                            id="searchButton">
                        Search Doctors →
                    </button>
                </div>
            </div>
            <p id="searchMessage" class="small mt-3 mb-0 text-primary"
               role="status" aria-live="polite"></p>
        </div>
    </div>
</section>

<!-- Trust Features -->
<section class="trust-section">
    <div class="container">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="trust-item">
                    <div class="trust-icon">🩺</div>
                    <div>
                        <strong>Experienced Doctors</strong>
                        <small>Find care by specialty</small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="trust-item">
                    <div class="trust-icon">🏥</div>
                    <div>
                        <strong>Medical Departments</strong>
                        <small>Explore available specialties</small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="trust-item">
                    <div class="trust-icon">📅</div>
                    <div>
                        <strong>Easy Appointments</strong>
                        <small>Plan your hospital visit</small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="trust-item">
                    <div class="trust-icon">🔒</div>
                    <div>
                        <strong>Privacy Matters</strong>
                        <small>Designed with privacy in mind</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Departments -->
<section class="section section-soft" id="departments">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">Our Departments</span>
            <h2>Find Care in Your Specialty</h2>
            <p>
                Explore medical specialties and find the right department
                for your healthcare needs.
            </p>
        </div>

        <div class="row g-3">
            <div class="col-6 col-lg-4 col-xl-2">
                <a class="department-card" href="#appointment"
                   data-department="Cardiology">
                    <div class="department-icon">❤️</div>
                    <h3>Cardiology</h3>
                    <p>Heart care</p>
                    <span class="department-arrow">Explore →</span>
                </a>
            </div>

            <div class="col-6 col-lg-4 col-xl-2">
                <a class="department-card" href="#appointment"
                   data-department="Neurology">
                    <div class="department-icon" style="background:#f0eaff">🧠</div>
                    <h3>Neurology</h3>
                    <p>Brain &amp; nerves</p>
                    <span class="department-arrow">Explore →</span>
                </a>
            </div>

            <div class="col-6 col-lg-4 col-xl-2">
                <a class="department-card" href="#appointment"
                   data-department="Orthopedics">
                    <div class="department-icon" style="background:#e6f7ff">🦴</div>
                    <h3>Orthopedics</h3>
                    <p>Bones &amp; joints</p>
                    <span class="department-arrow">Explore →</span>
                </a>
            </div>

            <div class="col-6 col-lg-4 col-xl-2">
                <a class="department-card" href="#appointment"
                   data-department="Pediatrics">
                    <div class="department-icon" style="background:#fff0e8">👶</div>
                    <h3>Pediatrics</h3>
                    <p>Child care</p>
                    <span class="department-arrow">Explore →</span>
                </a>
            </div>

            <div class="col-6 col-lg-4 col-xl-2">
                <a class="department-card" href="#appointment"
                   data-department="Dermatology">
                    <div class="department-icon" style="background:#e4f8ef">✨</div>
                    <h3>Dermatology</h3>
                    <p>Skin care</p>
                    <span class="department-arrow">Explore →</span>
                </a>
            </div>

            <div class="col-6 col-lg-4 col-xl-2">
                <a class="department-card" href="#appointment"
                   data-department="">
                    <div class="department-icon" style="background:#fff5d9">＋</div>
                    <h3>All Departments</h3>
                    <p>Explore specialties</p>
                    <span class="department-arrow">View all →</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section" id="how-it-works">
    <div class="container">
        <div class="section-heading">
            <span class="section-label">Simple &amp; Convenient</span>
            <h2>Your Appointment in Three Steps</h2>
            <p>Our planned booking workflow makes arranging a visit easier.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <h3>Find Your Doctor</h3>
                    <p>
                        Explore medical departments and identify a doctor
                        based on your healthcare needs.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">02</div>
                    <h3>Choose a Schedule</h3>
                    <p>
                        View available appointment times and choose a
                        suitable visit time.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">03</div>
                    <h3>Manage Your Visit</h3>
                    <p>
                        Review your appointment details and manage your
                        booking through your patient account.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta" id="about">
    <div class="container">
        <div class="cta-panel">
            <div>
                <span class="section-label">Your health comes first</span>
                <h2>Ready to take the next step?</h2>
                <p>Explore departments and start planning your hospital visit.</p>
            </div>
            <a href="#appointment" class="btn-primary-custom">
                Find a Doctor →
            </a>
        </div>
    </div>
</section>

</main>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="footer-brand">
                    <span style="color:#74a8ff">✚</span> MediBook.
                </div>
                <p class="footer-description">
                    A hospital appointment system designed to make
                    finding doctors and managing appointments simpler.
                </p>
            </div>

            <div class="col-6 col-lg-2">
                <div class="footer-heading">Quick Links</div>
                <div class="footer-links">
                    <a href="#home">Home</a>
                    <a href="#departments">Departments</a>
                    <a href="#how-it-works">How It Works</a>
                    <a href="#about">About Us</a>
                </div>
            </div>

            <div class="col-6 col-lg-2">
                <div class="footer-heading">Patient Care</div>
                <div class="footer-links">
                    <a href="#appointment">Find a Doctor</a>
                    <a href="#appointment">Appointments</a>
                    <a href="#departments">Specialties</a>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="footer-heading">Project Information</div>
                <p class="footer-description">
                    Developed as a Web Technologies college project
                    using PHP, MySQL, HTML, CSS and JavaScript.
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            &copy; <?= date('Y') ?> MediBook. All rights reserved.
            <br>Healthcare made simpler.
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Homepage department selection
    const departmentSelect = document.getElementById('departmentSearch');
    const doctorInput = document.getElementById('doctorSearch');
    const searchMessage = document.getElementById('searchMessage');

    document.querySelectorAll('[data-department]').forEach(function (card) {
        card.addEventListener('click', function () {
            departmentSelect.value = card.dataset.department;
        });
    });

    // Search UI preview: database-backed doctor search will be added later.
    document.getElementById('searchButton').addEventListener('click', function () {
        const department = departmentSelect.value;
        const doctor = doctorInput.value.trim();

        let message = 'Doctor search will be connected to the MediBook database in the next development stage.';

        if (department && doctor) {
            message = 'Selected department: ' + department +
                '. Doctor name: ' + doctor + '. ' + message;
        } else if (department) {
            message = 'Selected department: ' + department + '. ' + message;
        } else if (doctor) {
            message = 'Doctor name: ' + doctor + '. ' + message;
        }

        searchMessage.textContent = message;
    });
</script>

</body>
</html>
