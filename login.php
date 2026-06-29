<?php
session_start();

// Se já estiver logado, redireciona baseado no nível
if (isset($_SESSION['usuario_id'])) {
    switch ($_SESSION['usuario_nivel']) {
        case 'admin':
            header('Location: relatorios.php');
            break;
        case 'operador':
            header('Location: index.php');
            break;
        case 'visualizador':
            header('Location: painel.php');
            break;
        default:
            header('Location: painel.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Hospitalar</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #0ea5e9;
            --secondary: #6366f1;
            --danger: #ef4444;
            --success: #10b981;
            --dark: #0f172a;
            --light: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .login-container {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 3rem;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            animation: fadeInUp 0.8s ease;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(90deg, #0ea5e9, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #94a3b8;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: white;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);
        }

        .error-message {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid var(--danger);
            color: #ef4444;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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

        .info-box {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(255,255,255,0.03);
            border-radius: 8px;
            font-size: 0.9rem;
            color: #64748b;
            text-align: center;
        }

        .info-box strong {
            color: var(--primary);
        }

        .info-box small {
            display: block;
            margin-top: 0.5rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>UNISYSTEM - UPABJ</h1>
            <p>Faça login para acessar o sistema</p>
        </div>

        <div id="errorMessage" class="error-message"></div>

        <form id="loginForm">
            <div class="form-group">
                <label for="username">Usuário</label>
                <input type="text" id="username" name="username" required autocomplete="off" autofocus>
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                Entrar no Sistema
            </button>
        </form>

        <!--<div class="info-box">
            <strong>?? Acessos:</strong><br>
            admin / admin123 (Acesso total)<br>
            enfermagem / admin123 (Apenas Chamadas)<br>
            maqueiro / admin123 (Apenas Painel)
        </div>-->
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const btnLogin = document.getElementById('btnLogin');
            const errorDiv = document.getElementById('errorMessage');

            btnLogin.innerHTML = '<span class="loading"></span> Entrando...';
            btnLogin.disabled = true;
            errorDiv.classList.remove('show');

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (data.success) {
                    // Redirecionar baseado no nível do usuário
                    window.location.href = data.redirect;
                } else {
                    errorDiv.textContent = data.message || 'Usuário ou senha inválidos';
                    errorDiv.classList.add('show');
                    
                    btnLogin.innerHTML = 'Entrar no Sistema';
                    btnLogin.disabled = false;
                }
            } catch (error) {
                console.error('Erro:', error);
                errorDiv.textContent = 'Erro ao conectar com o servidor';
                errorDiv.classList.add('show');
                
                btnLogin.innerHTML = 'Entrar no Sistema';
                btnLogin.disabled = false;
            }
        });
    </script>
</body>
</html>