# 🏥 UNISYSTEM - Sistema de Chamada Inteligente

> Sistema de chamada hospitalar para UPABJ (Unidade de Pronto Atendimento) desenvolvido para gerenciar chamadas de **Médicos** e **Maqueiros** de forma inteligente e em tempo real.

---

## 📋 Índice

- [Sobre o Projeto](#sobre-o-projeto)
- [Funcionalidades](#funcionalidades)
- [Tecnologias Utilizadas](#tecnologias-utilizadas)
- [Arquitetura do Sistema](#arquitetura-do-sistema)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Estrutura de Pastas](#estrutura-de-pastas)
- [Uso](#uso)
- [API Endpoints](#api-endpoints)
- [Níveis de Acesso](#níveis-de-acesso)
- [PWA & Notificações](#pwa--notificações)
- [Screenshots](#screenshots)
- [Licença](#licença)

---

## 🎯 Sobre o Projeto

O **UNISYSTEM** é um sistema web completo para gerenciamento de chamadas hospitalares, permitindo que operadores solicitem a presença de médicos e maqueiros em setores específicos da unidade de saúde. O painel de exibição funciona como um aplicativo (PWA) com alertas sonoros, vibração e notificações nativas, garantindo que nenhuma chamada seja perdida.

### Principais Características
- ✅ Interface moderna e responsiva com tema escuro
- ✅ Chamadas em tempo real com polling inteligente
- ✅ Alertas sonoros com síntese de voz (TTS)
- ✅ Notificações push com vibração (mesmo em segundo plano)
- ✅ Painel PWA instalável em dispositivos móveis
- ✅ Relatórios completos com gráficos e exportação CSV
- ✅ Controle de acesso por níveis de usuário

---

## ✨ Funcionalidades

### 🔊 Sistema de Alertas
- **Alarme sonoro**: 3 bipes urgentes (grave → médio → agudo)
- **Síntese de voz**: Anuncia automaticamente "Médico/Maqueiro, dirija-se ao [setor]"
- **Vibração contínua**: Padrão de 3 pulsos fortes repetidos enquanto a chamada estiver ativa
- **Notificações nativas**: Via Service Worker, funcionam mesmo com o navegador minimizado
- **Web Worker**: Polling imune ao throttling do Chrome em background

### 📊 Painel de Chamadas
- Exibição em tempo real da chamada ativa
- Histórico das últimas 20 chamadas concluídas
- Indicador visual com animações (pulse, bounce)
- Contador de reprodução do áudio (1/3, 2/3, 3/3)

### 📈 Relatórios (Admin)
- Resumo estatístico (total, médicos, maqueiros, tempo médio)
- Gráfico de linha: chamadas por dia
- Gráfico de pizza: distribuição por tipo
- Tabela completa com filtros por período e tipo
- Exportação para CSV

---

## 🛠 Tecnologias Utilizadas

| Camada | Tecnologia |
|--------|-----------|
| **Backend** | PHP 7.4+ |
| **Banco de Dados** | SQLite |
| **Frontend** | HTML5, CSS3, JavaScript (Vanilla) |
| **Gráficos** | Chart.js |
| **PWA** | Service Worker, Web App Manifest |
| **Áudio** | Web Audio API, Speech Synthesis API |
| **Notificações** | Notifications API + Service Worker |
| **Fonte** | Inter (Google Fonts) |

---

## 🏗 Arquitetura do Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENTE                            │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │  index.php  │  │ painel.php  │  │   relatorios.php    │ │
│  │  (Operador) │  │(Visualizador)│  │      (Admin)        │ │
│  └──────┬──────┘  └──────┬──────┘  └──────────┬──────────┘ │
│         │                  │                     │            │
│  ┌──────▼──────────────────▼─────────────────────▼──────────┐│
│  │                    API (PHP)                            ││
│  │  login.php │ get_chamadas.php │ criar_chamada.php        ││
│  │  logout.php│ get_historico.php│ atualizar_chamada.php    ││
│  │  get_setores.php │ relatorios.php                      ││
│  └────────────────────────┬────────────────────────────────┘│
│                           │                                 │
│  ┌────────────────────────▼────────────────────────────────┐│
│  │              Service Worker (sw.js)                       ││
│  │     Notificações push + Keep-alive + Vibração           ││
│  └───────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                        SERVIDOR                              │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │  PHP Session + SQLite Database (database.db)           │ │
│  │  Tabelas: usuarios, setores, chamadas                  │ │
│  └─────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 Requisitos

- **PHP** 7.4 ou superior
- **Extensão PDO SQLite** habilitada
- **Navegador moderno** com suporte a:
  - Service Workers
  - Web Audio API
  - Speech Synthesis API
  - Notifications API
  - Vibration API (mobile)
- **HTTPS** (obrigatório para Service Workers e notificações push em produção)

---

## 🚀 Instalação

### 1. Clone ou copie os arquivos
```bash
git clone <repositorio>
cd unisystem
```

### 2. Configure o banco de dados
Certifique-se de que o arquivo `database.db` (SQLite) esteja na raiz do projeto com as seguintes tabelas:

```sql
-- Tabela de usuários
CREATE TABLE usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    nome TEXT NOT NULL,
    nivel TEXT NOT NULL CHECK(nivel IN ('admin', 'operador', 'visualizador'))
);

-- Tabela de setores
CREATE TABLE setores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL
);

-- Tabela de chamadas
CREATE TABLE chamadas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setor_id INTEGER NOT NULL,
    tipo_chamada TEXT NOT NULL CHECK(tipo_chamada IN ('medico', 'maqueiro')),
    status TEXT NOT NULL DEFAULT 'pendente' CHECK(status IN ('pendente', 'chamando', 'concluida')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (setor_id) REFERENCES setores(id)
);
```

### 3. Insira usuários iniciais
```sql
-- Exemplo de usuários (senhas devem ser hash com password_hash)
INSERT INTO usuarios (username, password, nome, nivel) VALUES
('admin', '$2y$10$...', 'Administrador', 'admin'),
('enfermagem', '$2y$10$...', 'Enfermagem', 'operador'),
('maqueiro', '$2y$10$...', 'Maqueiro', 'visualizador');
```

### 4. Configure as permissões
```bash
chmod 755 database.db
chmod 755 config/
chmod 755 api/
```

### 5. Acesse o sistema
```
http://localhost/unisystem/login.php
```

---

## ⚙ Configuração

### Arquivo `config/database.php`
Configure o caminho do banco de dados SQLite:
```php
private $db_file = __DIR__ . '/../database.db';
```

### Arquivo `config/acesso.php`
Controle de acesso por nível de usuário. Não requer alterações.

### Arquivo `config.js`
Ajuste os intervalos de verificação:
```javascript
const CONFIG = {
    CHECK_INTERVAL: 3000,      // 3 segundos entre verificações
    PAINEL_DURATION: 8000      // 8 segundos de exibição da chamada
};
```

---

## 📁 Estrutura de Pastas

```
unisystem/
│
├── 📄 index.php              # Página de chamada (operador)
├── 📄 painel.php             # Painel de exibição (visualizador)
├── 📄 relatorios.php         # Dashboard de relatórios (admin)
├── 📄 login.php              # Página de login
├── 📄 sw.js                  # Service Worker (PWA/notificações)
├── 📄 config.js              # Configurações globais JS
├── 📄 style.css              # Estilos adicionais
├── 📄 database.db            # Banco SQLite
│
├── 📁 api/                   # Endpoints da API
│   ├── login.php             # Autenticação
│   ├── logout.php            # Encerrar sessão
│   ├── get_setores.php       # Listar setores
│   ├── get_chamadas.php      # Buscar chamadas pendentes
│   ├── get_historico.php     # Histórico de chamadas
│   ├── criar_chamada.php     # Criar nova chamada
│   ├── atualizar_chamada.php # Atualizar status da chamada
│   ├── relatorios.php        # Dados para relatórios
│   └── exportar_csv.php    # Exportação CSV
│
└── 📁 config/                # Configurações PHP
    ├── acesso.php            # Funções de controle de acesso
    └── database.php          # Classe de conexão SQLite
```

---

## 🎮 Uso

### 👤 Login
1. Acesse `login.php`
2. Insira usuário e senha
3. O sistema redireciona automaticamente conforme o nível:
   - **Admin** → Relatórios
   - **Operador** → Fazer Chamada
   - **Visualizador** → Painel

### 📢 Fazer uma Chamada (Operador)
1. Clique no card **Médico** ou **Maqueiro**
2. Selecione o setor desejado no modal
3. Clique em **Confirmar Chamada**
4. A chamada será enviada ao painel em tempo real

### 📺 Painel de Chamadas (Visualizador)
1. Na primeira visita, clique em **"Ativar Alertas"**
2. Conceda permissão para notificações
3. O painel ficará em modo de espera
4. Quando uma chamada chegar:
   - 🔊 Toca alarme sonoro + voz
   - 📳 Vibra o dispositivo
   - 🔔 Mostra notificação nativa
   - 🖥️ Exibe a chamada no painel com animação

### 📊 Relatórios (Admin)
1. Acesse a aba **Relatórios**
2. Filtre por período e tipo de chamada
3. Visualize gráficos e tabela de dados
4. Exporte para CSV quando necessário

---

## 🔌 API Endpoints

| Método | Endpoint | Descrição | Parâmetros |
|--------|----------|-----------|------------|
| POST | `api/login.php` | Autenticação | `username`, `password` |
| POST | `api/logout.php` | Encerrar sessão | - |
| GET | `api/get_setores.php` | Listar setores | - |
| GET | `api/get_chamadas.php` | Chamadas pendentes | - |
| GET | `api/get_historico.php` | Últimas chamadas concluídas | - |
| POST | `api/criar_chamada.php` | Criar chamada | `setor_id`, `tipo_chamada` |
| POST | `api/atualizar_chamada.php` | Atualizar status | `id`, `status` |
| GET | `api/relatorios.php` | Dados para relatórios | `periodo`, `dataInicio`, `dataFim`, `tipo` |
| GET | `api/exportar_csv.php` | Exportar CSV | `dataInicio`, `dataFim` |

### Exemplo de Resposta (criar_chamada.php)
```json
{
  "success": true,
  "message": "Chamada criada com sucesso",
  "chamada": {
    "id": 42,
    "setor_id": 3,
    "tipo_chamada": "medico",
    "status": "pendente",
    "setor_nome": "Emergência",
    "created_at": "2026-06-29 14:30:00"
  }
}
```

---

## 🔐 Níveis de Acesso

| Nível | Páginas Acessíveis | Descrição |
|-------|-------------------|-----------|
| **admin** | Todas | Acesso total ao sistema |
| **operador** | index.php, login.php | Apenas fazer chamadas |
| **visualizador** | painel.php, login.php | Apenas visualizar o painel |

> ⚠️ Usuários tentando acessar páginas não autorizadas são redirecionados automaticamente.

---

## 📱 PWA & Notificações

O painel (`painel.php`) funciona como um **Progressive Web App**:

### Recursos PWA
- ✅ Instalável na tela inicial (Android/iOS)
- ✅ Funciona em tela cheia
- ✅ Service Worker para notificações em background
- ✅ Keep-alive do SW a cada 25 segundos
- ✅ Tema colorido na barra de status

### Fluxo de Ativação
```
Usuário acessa painel.php
        ↓
Modal "Ativar Alertas" aparece
        ↓
Clique no botão → Gesto do usuário
        ↓
├─ Cria AudioContext (desbloqueado)
├─ Solicita permissão de notificação
├─ Registra Service Worker
├─ Testa vibração + beep de confirmação
        ↓
Sistema ativo → Polling via Web Worker
```

### Compatibilidade de Notificações
| Plataforma | Notificação | Vibração | Áudio |
|-----------|-------------|----------|-------|
| Android Chrome | ✅ Sim | ✅ Sim (via SO) | ✅ Sim |
| iOS Safari | ⚠️ Limitado | ❌ Não | ✅ Sim |
| Desktop Chrome | ✅ Sim | ❌ Não | ✅ Sim |
| Desktop Firefox | ✅ Sim | ❌ Não | ✅ Sim |

---

## 🖼 Screenshots

> *Adicione screenshots do sistema aqui*

| Página | Preview |
|--------|---------|
| Login | `login.php` |
| Chamada | `index.php` |
| Painel | `painel.php` |
| Relatórios | `relatorios.php` |

---

## 📝 Notas Técnicas

### Síntese de Voz (TTS)
- Utiliza a API `SpeechSynthesis` do navegador
- Voz em português do Brasil (`pt-BR`)
- Velocidade reduzida (`rate: 0.9`) para melhor clareza
- Repete 3 vezes a mensagem da chamada

### Web Worker para Polling
- Evita o throttling do `setInterval` pelo Chrome em background
- Thread separada garante verificações a cada 2 segundos
- Não consome recursos da thread principal

### Service Worker
- Registrado na raiz do projeto (`/sw.js`)
- Scope: `/` (todo o domínio)
- Responde a pings de keep-alive para não ser morto pelo browser
- Exibe notificações nativas mesmo com a página fechada

---

## 🐛 Troubleshooting

### Problema: Notificações não aparecem
**Solução:**
1. Verifique se o site está em HTTPS
2. Confirme que `sw.js` está na raiz do projeto
3. Verifique permissões de notificação no navegador
4. Clique em "Ativar Alertas" novamente

### Problema: Áudio não toca
**Solução:**
1. O AudioContext precisa ser desbloqueado por gesto do usuário
2. Certifique-se de clicar em "Ativar Alertas"
3. Verifique se o navegador não está com som mutado

### Problema: Vibração não funciona
**Solução:**
- A vibração via `navigator.vibrate()` só funciona com a página visível
- Em background, a vibração ocorre via notificação do Service Worker
- iOS não suporta vibração via web

### Problema: Banco de dados não conecta
**Solução:**
1. Verifique se `database.db` existe na raiz
2. Confirme permissões de leitura/escrita
3. Verifique se a extensão `pdo_sqlite` está habilitada no PHP

---

## 📄 Licença

Este projeto é de uso interno da **UPABJ**.

---

## 👨‍💻 Desenvolvimento

Desenvolvido para a Unidade de Pronto Atendimento com foco em:
- 🚀 **Performance**: Polling otimizado e leve
- 🔊 **Acessibilidade**: Alertas multimodais (visual, sonoro, tátil)
- 📱 **Mobilidade**: PWA instalável em qualquer dispositivo
- 🔒 **Segurança**: Controle de acesso por sessão PHP

---

<div align="center">
  <strong>UNISYSTEM - UPABJ</strong><br>
  Sistema de Chamada Inteligente
</div>
