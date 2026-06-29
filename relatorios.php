<?php
session_start();
require_once 'config/acesso.php';

// Verificar se está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Apenas admin pode acessar relatórios
verificarAcesso(['admin']);

require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();

$usuario_nome = $_SESSION['usuario_nome'];
$usuario_nivel = $_SESSION['usuario_nivel'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema Hospitalar</title>
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
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding: 1rem 2rem;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            backdrop-filter: blur(10px);
        }

        .header h1 {
            font-size: 2rem;
            font-weight: 900;
            background: linear-gradient(90deg, #0ea5e9, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .user-name {
            color: #94a3b8;
        }

        .user-name strong {
            color: white;
            font-weight: 600;
        }

        .btn-sair {
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

        /* Navigation */
        .nav-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .nav-tab {
            padding: 1rem 2rem;
            border: 2px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
            color: white;
            border-radius: 16px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .nav-tab:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }

        .nav-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-color: transparent;
        }

        /* Cards de Resumo */
        .resumo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .resumo-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }

        .resumo-card h3 {
            color: #94a3b8;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .resumo-card .valor {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--primary);
        }

        .resumo-card .label {
            color: #64748b;
            font-size: 0.9rem;
        }

        /* Filtros */
        .filtros {
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .filtro-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filtro-group label {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .filtro-group select,
        .filtro-group input {
            padding: 0.75rem;
            background: rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            color: white;
            font-size: 1rem;
        }

        .btn-filtrar {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            align-self: flex-end;
        }

        /* Gráficos */
        .graficos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .grafico-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 1.5rem;
        }

        .grafico-card h3 {
            margin-bottom: 1rem;
            color: #94a3b8;
        }

        .grafico-placeholder {
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            border: 2px dashed rgba(255,255,255,0.1);
            border-radius: 12px;
        }

        /* Tabela */
        .tabela-container {
            background: rgba(255,255,255,0.05);
            border-radius: 20px;
            padding: 1.5rem;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 1rem;
            color: #94a3b8;
            font-weight: 600;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        tr:hover {
            background: rgba(255,255,255,0.02);
        }

        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge.medico {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
        }

        .badge.maqueiro {
            background: rgba(14, 165, 233, 0.2);
            color: #0ea5e9;
        }

        /* Botões de exportação */
        .export-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            justify-content: flex-end;
        }

        .btn-export {
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-export:hover {
            background: var(--primary);
        }

        /* Loading */
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

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .user-info {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .graficos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>UNISYSTEM - UPABJ</h1>
            <div class="user-info">
                <span class="user-name">
                     <strong><?= $usuario_nome ?></strong> (<?= $usuario_nivel ?>)
                </span>
                <button class="btn-sair" onclick="logout()">Sair</button>
            </div>
        </div>

        <nav class="nav-tabs">
            <a href="index.php" class="nav-tab">Fazer Chamada</a>
            <a href="painel.php" class="nav-tab">Painel</a>
            <a href="relatorios.php" class="nav-tab active">Relatórios</a>
        </nav>

        <!-- Cards de Resumo -->
        <div class="resumo-grid" id="resumoCards">
            <div class="resumo-card">
                <h3>Total de Chamadas</h3>
                <div class="valor" id="totalChamadas">-</div>
                <div class="label">últimos 30 dias</div>
            </div>
            <div class="resumo-card">
                <h3>Médicos</h3>
                <div class="valor" id="totalMedicos">-</div>
                <div class="label">chamadas</div>
            </div>
            <div class="resumo-card">
                <h3>Maqueiros</h3>
                <div class="valor" id="totalMaqueiros">-</div>
                <div class="label">chamadas</div>
            </div>
            <div class="resumo-card">
                <h3>Tempo Médio</h3>
                <div class="valor" id="tempoMedio">-</div>
                <div class="label">minutos por chamada</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filtros">
            <div class="filtro-group">
                <label>Período</label>
                <select id="filtroPeriodo">
                    <option value="hoje">Hoje</option>
                    <option value="semana">Últimos 7 dias</option>
                    <option value="mes" selected>Últimos 30 dias</option>
                    <option value="personalizado">Personalizado</option>
                </select>
            </div>
            <div class="filtro-group">
                <label>Data Início</label>
                <input type="date" id="dataInicio">
            </div>
            <div class="filtro-group">
                <label>Data Fim</label>
                <input type="date" id="dataFim">
            </div>
            <div class="filtro-group">
                <label>Tipo</label>
                <select id="filtroTipo">
                    <option value="todos">Todos</option>
                    <option value="medico">Médico</option>
                    <option value="maqueiro">Maqueiro</option>
                </select>
            </div>
            <button class="btn-filtrar" onclick="carregarDados()">Aplicar Filtros</button>
        </div>

        <!-- Gráficos -->
        <div class="graficos-grid">
            <div class="grafico-card">
                <h3>Chamadas por Dia</h3>
                <div class="grafico-placeholder" id="graficoLinha">
                    Carregando gráfico...
                </div>
            </div>
            <div class="grafico-card">
                <h3>Distribuição por Tipo</h3>
                <div class="grafico-placeholder" id="graficoPizza">
                    Carregando gráfico...
                </div>
            </div>
        </div>

        <!-- Tabela de Dados -->
        <div class="export-actions">
            <button class="btn-export" onclick="exportarCSV()">Exportar CSV</button>
            <button class="btn-export" onclick="exportarPDF()">Exportar PDF</button>
        </div>

        <div class="tabela-container">
            <table id="tabelaChamadas">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Data/Hora</th>
                        <th>Tipo</th>
                        <th>Setor</th>
                        <th>Status</th>
                        <th>Tempo (seg)</th>
                    </tr>
                </thead>
                <tbody id="tabelaBody">
                    <tr>
                        <td colspan="6" style="text-align: center;">Carregando dados...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

<!-- Chart.js para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let graficoLinha, graficoPizza;

    document.addEventListener('DOMContentLoaded', () => {
        const hoje = new Date();
        const mesPassado = new Date();
        mesPassado.setMonth(hoje.getMonth() - 1);
        
        // Formatar datas para YYYY-MM-DD
        document.getElementById('dataInicio').value = mesPassado.toISOString().split('T')[0];
        document.getElementById('dataFim').value = hoje.toISOString().split('T')[0];
        
        carregarDados();
    });

    async function carregarDados() {
        const periodo = document.getElementById('filtroPeriodo').value;
        const dataInicio = document.getElementById('dataInicio').value;
        const dataFim = document.getElementById('dataFim').value;
        const tipo = document.getElementById('filtroTipo').value;

        // Mostrar loading
        document.getElementById('tabelaBody').innerHTML = '<tr><td colspan="6" style="text-align: center;"><span class="loading"></span> Carregando...</td></tr>';
        document.getElementById('graficoLinha').innerHTML = 'Carregando gráfico...';
        document.getElementById('graficoPizza').innerHTML = 'Carregando gráfico...';

        try {
            console.log('Carregando dados com filtros:', { periodo, dataInicio, dataFim, tipo });
            
            const response = await fetch(`api/relatorios.php?periodo=${periodo}&dataInicio=${dataInicio}&dataFim=${dataFim}&tipo=${tipo}`);
            
            // Verificar se a resposta é OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Tentar parsear o JSON
            const text = await response.text();
            console.log('Resposta da API:', text.substring(0, 200) + '...'); // Log parcial
            
            let dados;
            try {
                dados = JSON.parse(text);
            } catch (e) {
                console.error('Erro ao parsear JSON:', text);
                throw new Error('Resposta não é JSON válido');
            }
            
            if (dados.error || !dados.success) {
                throw new Error(dados.error || 'Erro desconhecido');
            }
            
            atualizarResumo(dados.resumo);
            atualizarTabela(dados.chamadas);
            atualizarGraficos(dados);
            
        } catch (error) {
            console.error('Erro ao carregar dados:', error);
            document.getElementById('tabelaBody').innerHTML = `<tr><td colspan="6" style="text-align: center; color: #ef4444;">
                ? Erro ao carregar dados: ${error.message}<br>
                <button onclick="carregarDados()" style="margin-top: 10px; padding: 5px 10px; background: #0ea5e9; color: white; border: none; border-radius: 5px; cursor: pointer;">Tentar novamente</button>
            </td></tr>`;
            
            document.getElementById('graficoLinha').innerHTML = '? Erro ao carregar gráfico';
            document.getElementById('graficoPizza').innerHTML = '? Erro ao carregar gráfico';
        }
    }

    function atualizarResumo(resumo) {
        document.getElementById('totalChamadas').textContent = resumo.total || '0';
        document.getElementById('totalMedicos').textContent = resumo.medicos || '0';
        document.getElementById('totalMaqueiros').textContent = resumo.maqueiros || '0';
        document.getElementById('tempoMedio').textContent = resumo.tempoMedio || '0';
    }

    function atualizarTabela(chamadas) {
        const tbody = document.getElementById('tabelaBody');
        
        if (!chamadas || chamadas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94a3b8;">Nenhuma chamada encontrada no período</td></tr>';
            return;
        }

        tbody.innerHTML = chamadas.map(c => {
            const data = new Date(c.created_at + 'Z'); // Adicionar Z para UTC
            const dataStr = data.toLocaleDateString('pt-BR') + ' ' + data.toLocaleTimeString('pt-BR');
            
            let statusClass = '';
            let statusText = c.status;
            let statusIcon = '';
            
            switch(c.status) {
                case 'concluida':
                    statusClass = 'color: #10b981;';
                    statusText = 'Concluída';
                    statusIcon = '?';
                    break;
                case 'chamando':
                    statusClass = 'color: #0ea5e9;';
                    statusText = 'Chamando';
                    statusIcon = '??';
                    break;
                case 'pendente':
                    statusClass = 'color: #f59e0b;';
                    statusText = 'Pendente';
                    statusIcon = '?';
                    break;
                default:
                    statusIcon = '•';
            }
            
            const tipoIcon = c.tipo_chamada === 'medico' ? '?????' : '???';
            const tipoNome = c.tipo_chamada === 'medico' ? 'Médico' : 'Maqueiro';
            
            return `
                <tr>
                    <td>#${c.id}</td>
                    <td>${dataStr}</td>
                    <td><span class="badge ${c.tipo_chamada}">${tipoIcon} ${tipoNome}</span></td>
                    <td>${c.setor_nome}</td>
                    <td style="${statusClass}">${statusIcon} ${statusText}</td>
                    <td>${c.tempo_segundos ? c.tempo_segundos + 's' : '-'}</td>
                </tr>
            `;
        }).join('');
    }

    function atualizarGraficos(dados) {
        try {
            // Destruir gráficos existentes
            if (graficoLinha) graficoLinha.destroy();
            if (graficoPizza) graficoPizza.destroy();

            // Gráfico de Linha
            const ctxLinha = document.createElement('canvas');
            const graficoLinhaDiv = document.getElementById('graficoLinha');
            graficoLinhaDiv.innerHTML = '';
            graficoLinhaDiv.appendChild(ctxLinha);
            
            graficoLinha = new Chart(ctxLinha, {
                type: 'line',
                data: {
                    labels: dados.chartLabels || [],
                    datasets: [{
                        label: 'Chamadas por Dia',
                        data: dados.chartData || [],
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: 'white' }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255,255,255,0.1)' },
                            ticks: { color: 'white' }
                        },
                        x: {
                            grid: { color: 'rgba(255,255,255,0.1)' },
                            ticks: { color: 'white', maxRotation: 45, minRotation: 45 }
                        }
                    }
                }
            });

            // Gráfico de Pizza
            const ctxPizza = document.createElement('canvas');
            const graficoPizzaDiv = document.getElementById('graficoPizza');
            graficoPizzaDiv.innerHTML = '';
            graficoPizzaDiv.appendChild(ctxPizza);
            
            const medicos = dados.resumo?.medicos || 0;
            const maqueiros = dados.resumo?.maqueiros || 0;
            
            graficoPizza = new Chart(ctxPizza, {
                type: 'doughnut',
                data: {
                    labels: ['Médicos', 'Maqueiros'],
                    datasets: [{
                        data: [medicos, maqueiros],
                        backgroundColor: ['#10b981', '#0ea5e9'],
                        borderColor: 'transparent',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: 'white' }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = medicos + maqueiros;
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.error('Erro ao criar gráficos:', error);
        }
    }

    async function exportarCSV() {
        try {
            const dataInicio = document.getElementById('dataInicio').value;
            const dataFim = document.getElementById('dataFim').value;
            
            const response = await fetch(`api/exportar_csv.php?dataInicio=${dataInicio}&dataFim=${dataFim}`);
            const blob = await response.blob();
            
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `relatorio_${dataInicio}_a_${dataFim}.csv`;
            a.click();
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Erro ao exportar CSV:', error);
            alert('Erro ao exportar CSV. Tente novamente.');
        }
    }

    function exportarPDF() {
        alert('Exportação PDF será implementada em breve!');
    }

    function logout() {
        fetch('api/logout.php')
            .then(() => {
                window.location.href = 'login.php';
            })
            .catch(() => {
                window.location.href = 'login.php';
            });
    }

    // Adicionar listener para mudança no período
    document.getElementById('filtroPeriodo').addEventListener('change', function() {
        const hoje = new Date();
        const dataInicio = document.getElementById('dataInicio');
        const dataFim = document.getElementById('dataFim');
        
        switch(this.value) {
            case 'hoje':
                dataInicio.value = hoje.toISOString().split('T')[0];
                dataFim.value = hoje.toISOString().split('T')[0];
                break;
            case 'semana':
                const semanaPassada = new Date();
                semanaPassada.setDate(hoje.getDate() - 7);
                dataInicio.value = semanaPassada.toISOString().split('T')[0];
                dataFim.value = hoje.toISOString().split('T')[0];
                break;
            case 'mes':
                const mesPassado = new Date();
                mesPassado.setMonth(hoje.getMonth() - 1);
                dataInicio.value = mesPassado.toISOString().split('T')[0];
                dataFim.value = hoje.toISOString().split('T')[0];
                break;
            // personalizado mantém os valores atuais
        }
        
        carregarDados();
    });
</script>
</body>
</html>