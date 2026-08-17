<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Grocery App</title>
    <style>
        :root {
            --green-900: #0f5132;
            --green-700: #198754;
            --green-600: #1ea76d;
            --green-100: #e8f8f0;
            --orange-500: #ffb703;
            --dark: #1b1b1b;
            --muted: #6c757d;
            --white: #ffffff;
            --soft: #f7f8f8;
            --shadow: 0 20px 45px rgba(15, 81, 50, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(180deg, #f3fff8 0%, #ffffff 100%);
            color: var(--dark);
        }

        a {
            text-decoration: none;
        }

        .container {
            width: min(1200px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--green-900);
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--green-600), var(--green-700));
            color: var(--white);
            box-shadow: var(--shadow);
            font-size: 1.25rem;
        }

        .nav {
            display: flex;
            align-items: center;
            gap: 26px;
            color: var(--muted);
            font-size: 0.96rem;
        }

        .nav a {
            color: var(--muted);
        }

        .nav a:hover {
            color: var(--green-700);
        }

        .nav-cta {
            background: var(--green-700);
            color: var(--white) !important;
            border-radius: 999px;
            padding: 12px 20px;
            font-weight: 600;
            box-shadow: 0 12px 28px rgba(25, 135, 84, 0.2);
        }

        .hero {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            align-items: center;
            gap: 40px;
            padding: 34px 0 56px;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--green-100);
            color: var(--green-900);
            border: 1px solid rgba(25, 135, 84, 0.15);
            border-radius: 999px;
            padding: 9px 14px;
            font-size: 0.86rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        h1 {
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            line-height: 1.05;
            margin: 18px 0 18px;
            letter-spacing: -0.05em;
            color: var(--dark);
        }

        .highlight {
            color: var(--green-700);
        }

        .hero p {
            margin: 0;
            color: var(--muted);
            font-size: 1.08rem;
            line-height: 1.8;
            max-width: 580px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            margin-top: 28px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 24px;
            border-radius: 999px;
            font-weight: 700;
            transition: 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--green-600), var(--green-700));
            color: var(--white);
            box-shadow: 0 18px 30px rgba(30, 167, 109, 0.25);
        }

        .btn-secondary {
            border: 1px solid rgba(15, 81, 50, 0.15);
            color: var(--green-900);
            background: rgba(255, 255, 255, 0.7);
        }

        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 28px;
            margin-top: 30px;
        }

        .stat {
            min-width: 130px;
        }

        .stat strong {
            display: block;
            font-size: 1.8rem;
            color: var(--dark);
        }

        .stat span {
            color: var(--muted);
            font-size: 0.92rem;
        }

        .hero-visual {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 520px;
        }

        .mockup {
            position: relative;
            width: min(100%, 480px);
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(14, 75, 48, 0.08);
            border-radius: 32px;
            box-shadow: var(--shadow);
            padding: 22px;
            backdrop-filter: blur(10px);
        }

        .phone {
            background: linear-gradient(180deg, #ffffff 0%, #f4fff9 100%);
            border-radius: 28px;
            padding: 18px 18px 12px;
            border: 1px solid rgba(15, 81, 50, 0.08);
        }

        .top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #d8f7e6, #bee9d0);
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-weight: 700;
            color: var(--green-900);
        }

        .icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: #f3f5f5;
            display: grid;
            place-items: center;
            color: var(--green-900);
            font-size: 1.2rem;
        }

        .banner {
            background: linear-gradient(135deg, #dbfce8, #c8efdd);
            border-radius: 18px;
            padding: 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .banner h3 {
            margin: 0 0 5px;
            font-size: 1.15rem;
            color: var(--green-900);
        }

        .banner p {
            margin: 0;
            font-size: 0.7rem;
            color: var(--green-900);
            opacity: 0.8;
        }

        .badge {
            background: var(--white);
            border-radius: 999px;
            color: var(--green-700);
            font-size: 0.72rem;
            padding: 8px 10px;
            font-weight: 700;
        }

        .list {
            display: grid;
            gap: 12px;
        }

        .item {
            display: grid;
            grid-template-columns: 52px 1fr auto;
            gap: 12px;
            align-items: center;
            background: #ffffff;
            border: 1px solid rgba(15, 81, 50, 0.06);
            border-radius: 18px;
            padding: 10px 12px;
        }

        .item-image {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 1.4rem;
            background: linear-gradient(135deg, #fff0c9, #ffd76d);
        }

        .item h4 {
            margin: 0;
            font-size: 0.98rem;
            color: var(--dark);
        }

        .item p {
            margin: 4px 0 0;
            font-size: 0.74rem;
            color: var(--muted);
        }

        .price {
            font-weight: 800;
            color: var(--green-700);
            font-size: 0.9rem;
        }

        .floating-card {
            position: absolute;
            right: -8px;
            bottom: 24px;
            background: var(--white);
            border-radius: 18px;
            box-shadow: var(--shadow);
            padding: 14px 16px;
            min-width: 170px;
        }

        .floating-card small {
            display: block;
            color: var(--muted);
            margin-bottom: 8px;
            font-size: 0.72rem;
        }

        .floating-card strong {
            display: block;
            font-size: 1.6rem;
            color: var(--green-700);
            margin-bottom: 4px;
        }

        .floating-card span {
            color: var(--green-900);
            font-size: 0.8rem;
            font-weight: 600;
        }

        .features {
            padding: 32px 0 30px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 22px;
        }

        .section-title h2 {
            margin: 0;
            font-size: clamp(2rem, 4vw, 3rem);
            letter-spacing: -0.04em;
        }

        .section-title p {
            margin: 10px auto 0;
            max-width: 640px;
            color: var(--muted);
            line-height: 1.7;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 22px;
            margin-top: 26px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.72);
            border: 1px solid rgba(15, 81, 50, 0.08);
            border-radius: 26px;
            padding: 26px 22px;
            box-shadow: 0 8px 32px rgba(27, 27, 27, 0.04);
        }

        .feature-icon {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            margin-bottom: 16px;
            font-size: 1.7rem;
            background: linear-gradient(135deg, var(--green-100), #d8f7e6);
            color: var(--green-700);
        }

        .feature-card h3 {
            margin: 0 0 10px;
            font-size: 1.3rem;
        }

        .feature-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .cta {
            padding: 52px 0 80px;
        }

        .cta-box {
            background: linear-gradient(135deg, var(--green-900), var(--green-700));
            border-radius: 32px;
            padding: 42px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            box-shadow: var(--shadow);
        }

        .cta-box h2 {
            margin: 0 0 10px;
            color: var(--white);
            font-size: clamp(2rem, 3vw, 3rem);
            letter-spacing: -0.04em;
        }

        .cta-box p {
            margin: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
        }

        @media (max-width: 900px) {
            .hero {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero p {
                margin: 0 auto;
            }

            .hero-actions,
            .stats {
                justify-content: center;
            }

            .feature-grid {
                grid-template-columns: 1fr;
            }

            .cta-box {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 640px) {
            .nav {
                display: none;
            }

            .topbar {
                justify-content: center;
            }

            .cta-box {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div class="brand">
                <div class="brand-mark">🛒</div>
                <span>Grocery App</span>
            </div>

            <nav class="nav" aria-label="Main navigation">
                <a href="#features">Features</a>
                <a href="#about">About</a>
                <a href="#offers">Offers</a>
                <a href="#" class="nav-cta">Get Started</a>
            </nav>
        </header>

        <main>
            <section class="hero">
                <div>
                    <div class="tag">Fresh everyday</div>
                    <h1>Your daily <span class="highlight">groceries</span>, delivered fast.</h1>
                    <p>
                        Shop fresh produce, pantry staples, snacks, and essentials in minutes.
                        Smart delivery, better prices, and quality you can trust every single day.
                    </p>

                    <div class="hero-actions">
                        <a href="#" class="btn btn-primary">Shop Now</a>
                        <a href="#features" class="btn btn-secondary">Explore Features</a>
                    </div>

                    <div class="stats">
                        <div class="stat">
                            <strong>15k+</strong>
                            <span>happy users</span>
                        </div>
                        <div class="stat">
                            <strong>4.9/5</strong>
                            <span>average rating</span>
                        </div>
                        <div class="stat">
                            <strong>30 min</strong>
                            <span>delivery time</span>
                        </div>
                    </div>
                </div>

                <div class="hero-visual" aria-label="Grocery app preview">
                    <div class="mockup">
                        <div class="phone">
                            <div class="top-row">
                                <div class="avatar">A</div>
                                <div class="icon-btn">🔔</div>
                            </div>

                            <div class="banner">
                                <div>
                                    <h3>Groceries</h3>
                                    <p>Fresh deals today</p>
                                </div>
                                <div class="badge">20% OFF</div>
                            </div>

                            <div class="list">
                                <div class="item">
                                    <div class="item-image">🥬</div>
                                    <div>
                                        <h4>Organic Spinach</h4>
                                        <p>Fresh harvest</p>
                                    </div>
                                    <div class="price">$4.99</div>
                                </div>

                                <div class="item">
                                    <div class="item-image">🍋</div>
                                    <div>
                                        <h4>Citrus Mix</h4>
                                        <p>Vitamin packed</p>
                                    </div>
                                    <div class="price">$6.50</div>
                                </div>

                                <div class="item">
                                    <div class="item-image">🥖</div>
                                    <div>
                                        <h4>Whole Grain Bread</h4>
                                        <p>Bakery fresh</p>
                                    </div>
                                    <div class="price">$3.25</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="floating-card">
                        <small>Today's savings</small>
                        <strong>$58</strong>
                        <span>On your basket</span>
                    </div>
                </div>
            </section>

            <section id="features" class="features">
                <div class="section-title">
                    <h2>Everything you need, right at your fingertips.</h2>
                    <p>
                        From fresh fruit to household essentials, we bring the grocery store to your door with a seamless experience.
                    </p>
                </div>

                <div class="feature-grid">
                    <article class="feature-card">
                        <div class="feature-icon">🚚</div>
                        <h3>Fast Delivery</h3>
                        <p>Get your order delivered within minutes with smart route optimization and real-time tracking.</p>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon">🥬</div>
                        <h3>Fresh Quality</h3>
                        <p>We source premium products and carefully selected produce to keep every order fresh and reliable.</p>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon">💳</div>
                        <h3>Easy Payments</h3>
                        <p>Pay securely with multiple options like cards, wallet, or cash on delivery for convenient checkout.</p>
                    </article>
                </div>
            </section>

            <section class="cta" id="offers">
                <div class="cta-box">
                    <div>
                        <h2>Start saving on your next grocery trip.</h2>
                        <p>Discover exclusive deals, weekly discounts, and fresh picks made for your home.</p>
                    </div>
                    <a href="#" class="btn btn-primary">Join Now</a>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
