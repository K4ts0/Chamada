<?php
session_start();
require_once 'config/acesso.php';

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Usuários visualizador e admin podem acessar
verificarAcesso(['admin', 'visualizador']);

// Pegar informações do usuário
$usuario_nome = $_SESSION['usuario_nome'];
$usuario_nivel = $_SESSION['usuario_nivel'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Sistema Hospitalar</title>

    <!-- PWA: permite adicionar à tela inicial como app -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="UPABJ Painel">
    <meta name="theme-color" content="#0f172a">

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

        /* ===== MODAL DE ATIVAÇÃO ===== */
        #modalAtivacao {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 2rem;
        }

        .modal-box {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 2px solid rgba(14, 165, 233, 0.5);
            border-radius: 28px;
            padding: 3rem 2.5rem;
            text-align: center;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 0 60px rgba(14, 165, 233, 0.2);
            animation: modalEntrada 0.4s ease;
        }

        @keyframes modalEntrada {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-icon {
            font-size: 4.5rem;
            margin-bottom: 1.5rem;
            animation: pulse-icon 2s infinite;
        }

        @keyframes pulse-icon {
            0%, 100% { transform: scale(1); }
            50%       { transform: scale(1.1); }
        }

        .modal-box h2 {
            font-size: 1.8rem;
            font-weight: 900;
            margin-bottom: 1rem;
            background: linear-gradient(90deg, #0ea5e9, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .modal-box p {
            color: #94a3b8;
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .modal-items {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
            margin: 1.5rem 0;
            text-align: left;
        }

        .modal-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255,255,255,0.05);
            padding: 0.6rem 1rem;
            border-radius: 10px;
            font-size: 0.95rem;
            color: #cbd5e1;
        }

        #btnAtivarAlertas {
            width: 100%;
            padding: 1.1rem;
            background: linear-gradient(135deg, #0ea5e9, #6366f1);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.2rem;
            font-weight: 800;
            cursor: pointer;
            margin-top: 1rem;
            letter-spacing: 1px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 8px 30px rgba(14, 165, 233, 0.4);
        }

        #btnAtivarAlertas:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(14, 165, 233, 0.6);
        }

        #btnAtivarAlertas:active {
            transform: translateY(0);
        }

        /* Badge de status dos alertas */
        #statusAlertas {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        #statusAlertas.ativo {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border: 1px solid #10b981;
        }

        #statusAlertas.inativo {
            background: rgba(239, 68, 68, 0.2);
            color: #ef4444;
            border: 1px solid #ef4444;
            animation: pisca 2s infinite;
        }

        @keyframes pisca {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.5; }
        }

        /* ===== LAYOUT PRINCIPAL ===== */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

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
            z-index: 1000;
        }

        .btn-sair:hover {
            background: #ef4444;
            color: white;
        }

        .painel-container {
            background: rgba(0,0,0,0.3);
            border-radius: 32px;
            padding: 3rem;
            min-height: 70vh;
            border: 2px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }

        .painel-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .painel-header h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            border-radius: 50px;
            font-weight: 600;
        }

        .status-indicator::before {
            content: '';
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .chamada-ativa {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.2), rgba(99, 102, 241, 0.2));
            border: 2px solid var(--primary);
            border-radius: 24px;
            padding: 3rem;
            text-align: center;
            animation: chamadaPulse 2s infinite;
            display: none;
            margin-bottom: 2rem;
        }

        .chamada-ativa.active {
            display: block;
        }

        .chamada-ativa .icone {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: bounce 1s infinite;
        }

        .chamada-ativa .profissional {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .chamada-ativa .setor {
            font-size: 2.5rem;
            color: #fff;
            margin-bottom: 2rem;
            font-weight: bold;
        }

        .chamada-ativa .acao {
            font-size: 1.5rem;
            background: rgba(255,255,255,0.1);
            padding: 1rem 2rem;
            border-radius: 16px;
            display: inline-block;
        }

        .chamada-ativa .contador {
            margin-top: 1rem;
            font-size: 1.2rem;
            color: #94a3b8;
        }

        .aguardando {
            text-align: center;
            padding: 4rem;
            color: #64748b;
            border: 2px dashed rgba(255,255,255,0.1);
            border-radius: 24px;
            margin-bottom: 2rem;
        }

        .aguardando .icone {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .aguardando h3 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .historico {
            margin-top: 3rem;
        }

        .historico h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: #94a3b8;
        }

        .historico-lista {
            display: grid;
            gap: 1rem;
            max-height: 400px;
            overflow-y: auto;
        }

        .historico-item {
            background: rgba(255,255,255,0.05);
            border-left: 4px solid var(--primary);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            animation: slideIn 0.3s ease;
        }

        .historico-item.medico    { border-left-color: var(--success); }
        .historico-item.maqueiro  { border-left-color: var(--primary); }

        .historico-info h4 {
            font-size: 1.2rem;
            margin-bottom: 0.25rem;
        }

        .historico-info p {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .historico-hora {
            color: #64748b;
            font-weight: 600;
        }

        @keyframes fadeIn       { from { opacity: 0 } to { opacity: 1 } }
        @keyframes fadeInDown   { from { opacity: 0; transform: translateY(-30px) } to { opacity: 1; transform: translateY(0) } }
        @keyframes fadeInUp     { from { opacity: 0; transform: translateY(30px)  } to { opacity: 1; transform: translateY(0) } }
        @keyframes pulse        { 0%, 100% { opacity: 1 } 50% { opacity: 0.5 } }
        @keyframes chamadaPulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(14,165,233,0.4) }
            50%       { box-shadow: 0 0 0 30px rgba(14,165,233,0) }
        }
        @keyframes bounce       { 0%, 100% { transform: translateY(0) } 50% { transform: translateY(-15px) } }
        @keyframes slideIn      { from { opacity: 0; transform: translateX(-20px) } to { opacity: 1; transform: translateX(0) } }

        @media (max-width: 768px) {
            .header h1                  { font-size: 2rem; }
            .painel-container           { padding: 1.5rem; }
            .chamada-ativa .profissional{ font-size: 2rem; }
            .chamada-ativa .setor       { font-size: 1.8rem; }
        }
    </style>
</head>
<body>

    <!-- ====== MODAL DE ATIVAÇÃO OBRIGATÓRIA ====== -->
    <div id="modalAtivacao">
        <div class="modal-box">
            <div class="modal-icon">??</div>
            <h2>Ativar Alertas</h2>
            <p>Para receber chamadas como um aplicativo, precisamos de sua permissão para:</p>
            <div class="modal-items">
                <div class="modal-item">?? <span>Vibrar o dispositivo ao chegar chamada</span></div>
                <div class="modal-item">?? <span>Reproduzir alarme sonoro e voz</span></div>
                <div class="modal-item">?? <span>Mostrar notificação na tela (mesmo com painel fechado)</span></div>
            </div>
            <p style="font-size:0.85rem; color:#64748b;">Este passo é obrigatório pelos navegadores para garantir que alertas funcionem.</p>
            <button id="btnAtivarAlertas">?? ATIVAR ALERTAS AGORA</button>
        </div>
    </div>

    <!-- Badge de status -->
    <button id="statusAlertas" class="inativo" onclick="reabrirModal()" title="Clique para reativar alertas">
        ?? Alertas inativos
    </button>

    <button class="btn-sair" onclick="logout()">Sair</button>

    <div class="container">
        <header class="header">
            <h1>UNISYSTEM - PAINEL</h1>
            <p>Sistema de Chamada Inteligente - UPABJ</p>
            <div class="user-info">
                ?? <strong><?= $usuario_nome ?></strong> (<?= $usuario_nivel ?>)
                <?php if ($usuario_nivel === 'admin'): ?>
                    <a href="relatorios.php" style="margin-left: 1rem; color: #0ea5e9;">?? Relatórios</a>
                <?php endif; ?>
            </div>
        </header>

        <nav class="nav-tabs">
            <?php if ($usuario_nivel !== 'visualizador'): ?>
                <a href="index.php" class="nav-tab">Fazer Chamada</a>
            <?php endif; ?>
            <a href="painel.php" class="nav-tab active">Painel</a>
            <?php if ($usuario_nivel === 'admin'): ?>
                <a href="relatorios.php" class="nav-tab">Relatórios</a>
            <?php endif; ?>
        </nav>

        <div id="page-painel">
            <div class="painel-container">
                <div class="painel-header">
                    <h2>Painel de Chamadas</h2>
                    <div class="status-indicator">Sistema Ativo</div>
                </div>

                <div id="chamadaAtiva" class="chamada-ativa">
                    <div class="icone" id="iconeChamada">??</div>
                    <div class="profissional" id="profissionalChamada">-</div>
                    <div class="setor" id="setorChamada">-</div>
                    <div class="acao" id="acaoChamada">?? Chamada em andamento...</div>
                    <div class="contador" id="contadorAudio">Reproduzindo: 1/3</div>
                </div>

                <div id="aguardandoChamada" class="aguardando">
                    <div class="icone">??</div>
                    <h3>Aguardando chamadas...</h3>
                    <p>As chamadas aparecerão aqui automaticamente</p>
                </div>

                <div class="historico">
                    <h3>?? Últimas Chamadas</h3>
                    <div id="historicoLista" class="historico-lista"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // ================================================================
    //  ESTADO GLOBAL
    // ================================================================
    let chamadaAtual      = null;
    let processando       = false;
    let paginaAtiva       = true;
    let ultimasChamadas   = [];
    let audioReproduzindo = false;
    let notificacaoPermitida = false;
    let alertasAtivos     = false;

    // AudioContext GLOBAL — só criado após gesto do usuário
    let audioCtx = null;

    // Service Worker registration
    let swRegistration = null;

    // ================================================================
    //  MODAL DE ATIVAÇÃO — resolve o problema de "user gesture"
    // ================================================================
    document.getElementById('btnAtivarAlertas').addEventListener('click', async () => {
        await ativarTodosAlertas();
    });

    async function ativarTodosAlertas() {
        const btn = document.getElementById('btnAtivarAlertas');
        btn.textContent = 'Ativando...';
        btn.disabled = true;

        // 1. Criar e desbloquear AudioContext (PRECISA de gesto do usuário)
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') {
                await audioCtx.resume();
            }
            console.log('? AudioContext desbloqueado:', audioCtx.state);
        } catch (e) {
            console.warn('AudioContext falhou:', e);
        }

        // 2. Pedir permissão de notificação (PRECISA de gesto do usuário)
        if ('Notification' in window) {
            try {
                const perm = await Notification.requestPermission();
                notificacaoPermitida = (perm === 'granted');
                console.log('?? Permissão notificação:', perm);
            } catch (e) {
                console.warn('Permissão notificação falhou:', e);
            }
        }

        // 3. Registrar Service Worker
        await registrarServiceWorker();

        // 4. Testar vibração imediatamente (confirmação háptica)
        vibrar([100, 80, 100, 80, 200]);

        // 5. Tocar beep de confirmação
        tocarBeepConfirmacao();

        // 6. Fechar modal e marcar como ativo
        alertasAtivos = true;
        document.getElementById('modalAtivacao').style.display = 'none';
        atualizarBadgeStatus();

        // 7. Iniciar sistema
        buscarChamadaPendente();
        carregarHistorico();

        // 8. Polling via Web Worker (imune ao throttling do Chrome em background)
        iniciarPollingWorker();

        // 9. Keep-alive do Service Worker (evita que o SW adormeça)
        iniciarKeepAliveSW();

        console.log('?? Sistema de alertas ativado!');
    }

    function reabrirModal() {
        document.getElementById('modalAtivacao').style.display = 'flex';
        document.getElementById('btnAtivarAlertas').textContent = '?? ATIVAR ALERTAS AGORA';
        document.getElementById('btnAtivarAlertas').disabled = false;
    }

    function atualizarBadgeStatus() {
        const badge = document.getElementById('statusAlertas');
        if (alertasAtivos && notificacaoPermitida) {
            badge.className = 'ativo';
            badge.textContent = '? Alertas ativos';
        } else if (alertasAtivos) {
            badge.className = 'ativo';
            badge.textContent = '?? Áudio + Vibração ativos';
        } else {
            badge.className = 'inativo';
            badge.textContent = '?? Alertas inativos';
        }
    }

    // ================================================================
    //  SERVICE WORKER
    // ================================================================
    async function registrarServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            console.log('Service Worker não suportado');
            return;
        }
        try {
            swRegistration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
            console.log('? Service Worker registrado:', swRegistration.scope);
        } catch (e) {
            console.warn('Service Worker falhou (verifique se sw.js existe na raiz):', e);
        }
    }

    // ================================================================
    //  VIBRAÇÃO DIRETA (funciona apenas com página em primeiro plano)
    //  Serve como complemento — a vibração via notificação cobre o background
    // ================================================================
    function vibrar(padrao) {
        if ('vibrate' in navigator) {
            navigator.vibrate(padrao);
            console.log('?? Vibrando direto:', padrao);
        }
    }

    // Vibração contínua enquanto a chamada estiver ativa (página visível)
    let vibracaoInterval = null;

    function iniciarVibracaoContinua() {
        // Disparo imediato
        vibrar(VIBRATE_PADRAO);
        // Repete a cada 4s enquanto o áudio estiver tocando e a página visível
        vibracaoInterval = setInterval(() => {
            if (audioReproduzindo && !document.hidden) {
                vibrar(VIBRATE_PADRAO);
            } else {
                pararVibracao();
            }
        }, 4000);
    }

    function pararVibracao() {
        if (vibracaoInterval) {
            clearInterval(vibracaoInterval);
            vibracaoInterval = null;
        }
        if ('vibrate' in navigator) navigator.vibrate(0);
    }

    // ================================================================
    //  ÁUDIO — usa o AudioContext já desbloqueado
    // ================================================================

    // Beep de confirmação (quando o usuário clica em "Ativar")
    function tocarBeepConfirmacao() {
        if (!audioCtx) return;
        try {
            const osc  = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'sine';
            osc.frequency.value = 880;
            gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.3);
        } catch(e) {}
    }

    // Alarme de chamada — toca 3 bipes urgentes antes da voz
    async function tocarAlarme() {
        if (!audioCtx) return;
        try {
            // AudioContext suspende após inatividade — resume() garante que toca
            if (audioCtx.state === 'suspended') {
                await audioCtx.resume();
                console.log('?? AudioContext reativado após inatividade');
            }
            const now = audioCtx.currentTime;

            function bipe(inicio, freq, duracao) {
                const osc  = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'square';
                osc.frequency.value = freq;
                gain.gain.setValueAtTime(0, inicio);
                gain.gain.linearRampToValueAtTime(0.4, inicio + 0.02);
                gain.gain.linearRampToValueAtTime(0,   inicio + duracao);
                osc.start(inicio);
                osc.stop(inicio + duracao + 0.05);
            }

            // 3 bipes urgentes: grave ? médio ? agudo
            bipe(now,        660, 0.18);
            bipe(now + 0.28, 880, 0.18);
            bipe(now + 0.56, 1100, 0.25);

            // Repetir mais uma vez
            bipe(now + 1.0,  660, 0.18);
            bipe(now + 1.28, 880, 0.18);
            bipe(now + 1.56, 1100, 0.25);

        } catch (e) {
            console.warn('Alarme falhou:', e);
        }
    }

    // ================================================================
    //  NOTIFICAÇÃO NATIVA — via registration.showNotification()
    //  Este método funciona mesmo com Chrome em segundo plano pois
    //  acorda o Service Worker e dispara a vibração via SO (Android).
    //  O padrão [600,200,600,200,600] = 3 vibrações de 600ms.
    // ================================================================

    // 3 pulsos fortes — reconhecível mesmo no bolso
    const VIBRATE_PADRAO = [600, 200, 600, 200, 600];

    async function mostrarNotificacao(chamada) {
        if (!notificacaoPermitida) {
            console.warn('?? Notificação não permitida — alertas só por áudio');
            return;
        }

        const tipo  = chamada.tipo_chamada === 'medico' ? 'Médico' : 'Maqueiro';
        const icone = chamada.tipo_chamada === 'medico' ? '?????' : '??';

        const titulo = `?? CHAMADA: ${tipo.toUpperCase()}`;
        const corpo  = `${icone} ${tipo} — Dirija-se ao ${chamada.setor_nome}`;

        const tag = `chamada-${chamada.id}`;

        const opcoes = {
            body:               corpo,
            icon:               '/favicon.ico',
            badge:              '/favicon.ico',
            tag:                tag,
            renotify:           true,
            requireInteraction: true,
            silent:             false,
            vibrate:            VIBRATE_PADRAO,
            data:               { url: window.location.href }
        };

        // Android Chrome ignora requireInteraction e fecha a notificação em ~2s.
        // Solução: reapresentar com a mesma tag antes de sumir, estendendo
        // o tempo total visível para 5 segundos.
        const DURACAO_MS = 5000;  // duração total desejada
        const REFRESH_MS = 2200;  // reapresenta antes do Android fechar (~2s)

        async function exibirNotif() {
            if (swRegistration) {
                try {
                    await swRegistration.showNotification(titulo, opcoes);
                    return;
                } catch (e) {
                    console.warn('SW showNotification falhou:', e);
                }
            }
            try {
                const notif = new Notification(titulo, opcoes);
                notif.onclick = () => { window.focus(); notif.close(); };
            } catch (e) {
                console.warn('Notificação direta falhou:', e);
            }
        }

        // 1ª exibição imediata
        await exibirNotif();
        console.log('?? Notificação disparada — mantendo por', DURACAO_MS / 1000, 's');

        // Re-exibições para cobrir 5s (Android fecha a cada ~2s)
        const refreshes = Math.floor(DURACAO_MS / REFRESH_MS) - 1;
        for (let i = 1; i <= refreshes; i++) {
            setTimeout(() => exibirNotif(), REFRESH_MS * i);
        }

        // Fecha definitivamente após 5s
        setTimeout(async () => {
            if (swRegistration) {
                const notifs = await swRegistration.getNotifications({ tag });
                notifs.forEach(n => n.close());
            }
        }, DURACAO_MS);
    }

    // ================================================================
    //  BUSCA DE CHAMADAS
    // ================================================================
    async function buscarChamadaPendente() {
        if (!alertasAtivos) return; // não busca antes do usuário ativar
        try {
            const response = await fetch('api/get_chamadas.php');
            const chamadas = await response.json();

            if (chamadas && chamadas.length > 0) {
                const chamada = chamadas[0];

                if (!ultimasChamadas.includes(chamada.id) && chamada.status === 'pendente') {
                    console.log('?? NOVA CHAMADA DETECTADA!', chamada);

                    // -- 1. Vibração de alarme (contínua)
                    iniciarVibracaoContinua();

                    // -- 2. Notificação nativa (via SW ou API)
                    mostrarNotificacao(chamada);

                    // -- 3. Alarme sonoro
                    tocarAlarme();

                    // -- 4. Título da aba
                    if (!paginaAtiva) {
                        document.title = '?? NOVA CHAMADA! - Painel';
                    }

                    if (!processando && !chamadaAtual && !audioReproduzindo) {
                        processando = true;
                        await processarChamada(chamada);
                    }
                }
            }
        } catch (error) {
            console.error('Erro ao buscar chamadas:', error);
        }
    }

    // ================================================================
    //  PROCESSAR CHAMADA
    // ================================================================
    async function processarChamada(chamada) {
        chamadaAtual = chamada;
        ultimasChamadas.push(chamada.id);
        try { localStorage.setItem('ultimasChamadas', JSON.stringify(ultimasChamadas)); } catch(e) {}

        const chamadaDiv    = document.getElementById('chamadaAtiva');
        const aguardandoDiv = document.getElementById('aguardandoChamada');
        const icone         = document.getElementById('iconeChamada');
        const profissional  = document.getElementById('profissionalChamada');
        const setor         = document.getElementById('setorChamada');
        const contador      = document.getElementById('contadorAudio');

        if (chamada.tipo_chamada === 'medico') {
            icone.textContent      = '?????';
            profissional.textContent = 'MÉDICO';
        } else {
            icone.textContent      = '??';
            profissional.textContent = 'MAQUEIRO';
        }

        setor.textContent = chamada.setor_nome;
        aguardandoDiv.style.display = 'none';
        chamadaDiv.classList.add('active');

        await atualizarStatus(chamada.id, 'chamando');

        audioReproduzindo = true;
        await reproduzirAudio(chamada, 3, contador);

        await finalizarChamada(chamada);
        document.title = 'Painel - Sistema Hospitalar';
    }

    // ================================================================
    //  SÍNTESE DE VOZ
    // ================================================================
    function reproduzirAudio(chamada, repeticoes, elementoContador) {
        return new Promise((resolve) => {
            const texto = `${chamada.tipo_chamada === 'medico' ? 'Médico' : 'Maqueiro'}, dirija-se ao ${chamada.setor_nome}`;
            let contador = 0;

            // Garantir que speechSynthesis está desbloqueado
            if (window.speechSynthesis.paused) {
                window.speechSynthesis.resume();
            }

            function falar() {
                if (contador < repeticoes) {
                    if (elementoContador) {
                        elementoContador.textContent = `Reproduzindo: ${contador + 1}/${repeticoes}`;
                    }

                    const utterance = new SpeechSynthesisUtterance(texto);
                    utterance.lang  = 'pt-BR';
                    utterance.rate  = 0.9;
                    utterance.pitch = 1;
                    utterance.volume = 1;

                    // Escolher voz em português se disponível
                    const vozes = window.speechSynthesis.getVoices();
                    const vozPT = vozes.find(v => v.lang.startsWith('pt'));
                    if (vozPT) utterance.voice = vozPT;

                    utterance.onend = () => {
                        contador++;
                        if (contador < repeticoes) {
                            setTimeout(falar, 800);
                        } else {
                            if (elementoContador) elementoContador.textContent = 'Chamada concluída';
                            audioReproduzindo = false;
                            pararVibracao();
                            setTimeout(resolve, 1000);
                        }
                    };

                    utterance.onerror = () => {
                        contador++;
                        if (contador < repeticoes) {
                            setTimeout(falar, 800);
                        } else {
                            audioReproduzindo = false;
                            pararVibracao();
                            resolve();
                        }
                    };

                    window.speechSynthesis.speak(utterance);
                }
            }

            // Pequeno delay para o alarme sonoro terminar primeiro
            setTimeout(falar, 2200);
        });
    }

    // ================================================================
    //  FINALIZAR CHAMADA
    // ================================================================
    async function finalizarChamada(chamada) {
        await atualizarStatus(chamada.id, 'concluida');
        document.getElementById('chamadaAtiva').classList.remove('active');
        document.getElementById('aguardandoChamada').style.display = 'block';
        adicionarAoHistorico(chamada);
        processando  = false;
        chamadaAtual = null;
    }

    async function atualizarStatus(id, status) {
        try {
            await fetch('api/atualizar_chamada.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
        } catch (e) {
            console.error('Erro ao atualizar status:', e);
        }
    }

    // ================================================================
    //  HISTÓRICO
    // ================================================================
    async function carregarHistorico() {
        try {
            const response = await fetch('api/get_historico.php');
            const chamadas = await response.json();
            const lista = document.getElementById('historicoLista');
            lista.innerHTML = '';
            chamadas.forEach(c => adicionarAoHistorico(c, true));
        } catch (e) {
            console.error('Erro ao carregar histórico:', e);
        }
    }

    function adicionarAoHistorico(chamada, ignorarDuplicata = false) {
        const lista = document.getElementById('historicoLista');

        if (!ignorarDuplicata) {
            if (document.querySelector(`.historico-item[data-id="${chamada.id}"]`)) return;
        }

        const item = document.createElement('div');
        item.className = `historico-item ${chamada.tipo_chamada}`;
        item.setAttribute('data-id', chamada.id);

        let data = new Date(chamada.created_at.includes('Z') ? chamada.created_at : chamada.created_at + 'Z');
        const hora = data.toLocaleTimeString('pt-BR', {
            hour: '2-digit', minute: '2-digit', second: '2-digit',
            timeZone: 'America/Sao_Paulo'
        });

        const icone = chamada.tipo_chamada === 'medico' ? '?????' : '??';
        const nome  = chamada.tipo_chamada === 'medico' ? 'Médico' : 'Maqueiro';

        item.innerHTML = `
            <div class="historico-info">
                <h4>${icone} ${nome}</h4>
                <p>${chamada.setor_nome}</p>
            </div>
            <div class="historico-hora">${hora}</div>
        `;

        lista.insertBefore(item, lista.firstChild);
        while (lista.children.length > 20) lista.removeChild(lista.lastChild);
    }

    // ================================================================
    //  VISIBILIDADE DA PÁGINA
    // ================================================================
    document.addEventListener('visibilitychange', () => {
        paginaAtiva = !document.hidden;
        if (paginaAtiva) {
            document.title = 'Painel - Sistema Hospitalar';
            // Ao voltar ao foco, checa imediatamente
            if (alertasAtivos && !processando && !chamadaAtual && !audioReproduzindo) {
                buscarChamadaPendente();
            }
        }
    });

    // ================================================================
    //  POLLING VIA WEB WORKER
    //  O setInterval da thread principal é THROTTLED pelo Chrome após
    //  alguns minutos em segundo plano (pode ir de 2s para 60s+).
    //  Web Workers rodam numa thread separada e NÃO são throttled.
    // ================================================================
    function iniciarPollingWorker() {
        const codigo = `
            let intervalo = null;
            self.onmessage = function(e) {
                if (e.data === 'start' && !intervalo) {
                    intervalo = setInterval(() => self.postMessage('tick'), 2000);
                }
                if (e.data === 'stop' && intervalo) {
                    clearInterval(intervalo);
                    intervalo = null;
                }
            };
        `;
        const blob   = new Blob([codigo], { type: 'application/javascript' });
        const worker = new Worker(URL.createObjectURL(blob));

        worker.onmessage = () => {
            if (alertasAtivos && !processando && !chamadaAtual && !audioReproduzindo) {
                buscarChamadaPendente();
            }
        };

        worker.postMessage('start');
        console.log('?? Web Worker de polling iniciado (imune ao throttling)');
        return worker;
    }

    // ================================================================
    //  KEEP-ALIVE DO SERVICE WORKER
    //  O browser mata o SW após ~30s de inatividade. Mandamos um ping
    //  a cada 25s para mantê-lo vivo e pronto para vibrar/notificar.
    // ================================================================
    function iniciarKeepAliveSW() {
        setInterval(() => {
            if (swRegistration && swRegistration.active) {
                swRegistration.active.postMessage({ type: 'KEEPALIVE' });
            }
        }, 25000);
    }

    // ================================================================
    //  CARREGAR IDs DO localStorage
    // ================================================================
    try {
        const stored = localStorage.getItem('ultimasChamadas');
        if (stored) ultimasChamadas = JSON.parse(stored);
    } catch (e) {}

    // ================================================================
    //  LOGOUT
    // ================================================================
    function logout() {
        fetch('api/logout.php').then(() => { window.location.href = 'login.php'; });
    }
    </script>
</body>
</html>