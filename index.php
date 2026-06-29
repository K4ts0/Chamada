<?php
session_start();
require_once 'config/acesso.php';

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Usuários operador e admin podem acessar
verificarAcesso(['admin', 'operador']);

// Pegar informações do usuário
$usuario_nome = $_SESSION['usuario_nome'];
$usuario_nivel = $_SESSION['usuario_nivel'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamada - Sistema Hospitalar</title>
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
            color: white;
            overflow-x: hidden;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInDown 0.8s ease;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 900;
            background: linear-gradient(90deg, #0ea5e9, #6366f1, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #94a3b8;
            font-size: 1.1rem;
        }

        .user-info {
            margin-top: 1rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.05);
            border-radius: 50px;
            display: inline-block;
            color: #94a3b8;
        }

        .user-info strong {
            color: var(--primary);
        }

        /* Navigation */
        .nav-tabs {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease 0.2s both;
        }

        .nav-tab {
            padding: 1rem 2rem;
            border: 2px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: white;
            border-radius: 16px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            text-decoration: none;
            display: inline-block;
        }

        .nav-tab:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .nav-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: transparent;
            box-shadow: 0 10px 40px rgba(14, 165, 233, 0.3);
        }

        /* Chamada Page */
        .chamada-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1000px;
            margin: 0 auto;
        }

        .card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.4s ease;
            backdrop-filter: blur(10px);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .card:hover::before {
            transform: translateX(100%);
        }

        .card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 60px rgba(14, 165, 233, 0.2);
            border-color: rgba(14, 165, 233, 0.5);
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .card h2 {
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .card p {
            color: #94a3b8;
        }

        .card.medico {
            border-left: 4px solid var(--success);
        }

        .card.maqueiro {
            border-left: 4px solid var(--primary);
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(10px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 24px;
            padding: 2.5rem;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 100px rgba(0,0,0,0.5);
            animation: scaleIn 0.3s ease;
        }

        .modal h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-align: center;
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

        .form-group select, .form-group input {
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

        .form-group select option {
            background: #1e293b;
            color: white;
        }

        .form-group select:focus, .form-group input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.2);
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);
        }

        .btn-secondary {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.2);
        }

        .btn-sair {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 0.5rem 1rem;
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid #ef4444;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-sair:hover {
            background: #ef4444;
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
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

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            z-index: 9999;
            font-weight: 600;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2rem;
            }

            .nav-tabs {
                flex-direction: column;
                align-items: center;
            }

            .chamada-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <button class="btn-sair" onclick="logout()">Sair</button>

    <div class="container">
        <header class="header">
            <h1>UNISYSTEM - Fazer Chamada </h1>
            <p>Sistema de Chamada Inteligente - UPABJ</p>
            <div class="user-info">
                ?? <strong><?= $usuario_nome ?></strong> (<?= $usuario_nivel ?>)
                <?php if ($usuario_nivel === 'admin'): ?>
                    <a href="relatorios.php" style="margin-left: 1rem; color: #0ea5e9;">?? Relatórios</a>
                <?php endif; ?>
            </div>
        </header>

        <nav class="nav-tabs">
            <a href="index.php" class="nav-tab active">Fazer Chamada</a>
            <a href="painel.php" class="nav-tab">Painel</a>
            <?php if ($usuario_nivel === 'admin'): ?>
                <a href="relatorios.php" class="nav-tab">Relatórios</a>
            <?php endif; ?>
        </nav>

        <!-- Página de Chamada -->
        <div id="page-chamada">
            <div class="chamada-container">
                <div class="card medico" onclick="openModal('medico')">
                    <div class="card-icon">??</div>
                    <h2>Médico</h2>
                    <p>Chamar médico para atendimento</p>
                </div>

                <div class="card maqueiro" onclick="openModal('maqueiro')">
                    <div class="card-icon">???</div>
                    <h2>Maqueiro</h2>
                    <p>Chamar maqueiro para transporte</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Chamada -->
    <div id="modal" class="modal-overlay">
        <div class="modal">
            <h2 id="modalTitle">Fazer Chamada</h2>

            <div class="form-group">
                <label for="setorSelect">Selecione o Setor:</label>
                <select id="setorSelect" required>
                    <option value="">Carregando setores...</option>
                </select>
            </div>

            <div class="btn-group">
                <button class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-primary" onclick="confirmarChamada()">
                    Confirmar Chamada
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentTipo = '';
        
        document.addEventListener('DOMContentLoaded', async () => {
            await carregarSetores();
        });

        async function carregarSetores() {
            try {
                const response = await fetch('api/get_setores.php');
                const setores = await response.json();
                
                const select = document.getElementById('setorSelect');
                select.innerHTML = '<option value="">Selecione um setor...</option>';
                
                setores.forEach(setor => {
                    const option = document.createElement('option');
                    option.value = setor.id;
                    option.textContent = setor.nome;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Erro ao carregar setores:', error);
                document.getElementById('setorSelect').innerHTML = '<option value="">Erro ao carregar setores</option>';
            }
        }

        function openModal(tipo) {
            currentTipo = tipo;
            const modal = document.getElementById('modal');
            const title = document.getElementById('modalTitle');

            if (tipo === 'medico') {
                title.textContent = 'Chamar Médico';
            } else {
                title.textContent = 'Chamar Maqueiro';
            }

            modal.classList.add('active');
        }

        function closeModal() {
            document.getElementById('modal').classList.remove('active');
            document.getElementById('setorSelect').value = '';
        }

        async function confirmarChamada() {
            const setorId = document.getElementById('setorSelect').value;
            
            if (!setorId) {
                alert('Por favor, selecione um setor!');
                return;
            }

            const btnConfirmar = document.querySelector('.btn-primary');
            const textoOriginal = btnConfirmar.innerHTML;
            btnConfirmar.innerHTML = '<span class="loading"></span> Enviando...';
            btnConfirmar.disabled = true;

            try {
                const response = await fetch('api/criar_chamada.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        setor_id: setorId,
                        tipo_chamada: currentTipo
                    })
                });

                const data = await response.json();

                if (data.success) {
                    closeModal();
                    showNotification('Chamada realizada com sucesso!');
                    document.getElementById('setorSelect').value = '';
                } else {
                    alert('Erro ao realizar chamada: ' + data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao conectar com o servidor');
            } finally {
                btnConfirmar.innerHTML = textoOriginal;
                btnConfirmar.disabled = false;
            }
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideInRight 0.3s ease reverse';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function logout() {
            fetch('api/logout.php')
                .then(() => {
                    window.location.href = 'login.php';
                });
        }

        document.getElementById('modal').addEventListener('click', (e) => {
            if (e.target.id === 'modal') {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>