<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lapor Bersih Kampus</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #e8f5e9, #f5faf5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        .logo {
            width: 65px;
            height: 65px;
            background: #198754;
            color: white;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            margin: 0 auto 18px;
        }

        .login-title {
            color: #198754;
            font-weight: bold;
        }

        .form-control {
            padding: 12px;
            border-radius: 9px;
        }

        .btn-login {
            background: #198754;
            color: white;
            padding: 12px;
            border-radius: 9px;
            font-weight: bold;
        }

        .btn-login:hover {
            background: #146c43;
            color: white;
        }
    </style>
</head>

<body>

    <div class="login-card">

        <div class="text-center">
            <div class="logo">♻</div>

            <h3 class="login-title">Lapor Bersih Kampus</h3>

            <p class="text-muted mb-4">
                Silakan login untuk melanjutkan
            </p>
        </div>

        <?php if (session()->getFlashdata('error')) : ?>
            <div class="alert alert-danger">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')) : ?>
            <div class="alert alert-success">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')) : ?>
            <div class="alert alert-danger">
                <?php foreach (session()->getFlashdata('errors') as $error) : ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('login') ?>" method="post">

            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>

                <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="Masukkan email"
                    value="<?= old('email') ?>"
                    required
                >
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>

                <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-login w-100">
                Login
            </button>

        </form>

        <div class="text-center mt-4">
            <small class="text-muted">
                &copy; <?= date('Y') ?> Lapor Bersih Kampus
            </small>
        </div>

    </div>

</body>
</html>