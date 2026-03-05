# 📦 Consorti API - Sistema de Gestão Local

> **Sistema completo de gestão de estoque, vendas e relatórios para varejo local.**  
> Desenvolvido para **Consorti Equipamentos** - Solução local sem dependência de internet.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-Proprietário-red?style=for-the-badge)

---

## 📋 Índice

- [🎯 Visão Geral](#-visão-geral)
- [🚀 Funcionalidades](#-funcionalidades)
- [🛠️ Tecnologias](#️-tecnologias)
- [📁 Estrutura do Projeto](#-estrutura-do-projeto)
- [📦 Instalação](#-instalação)
- [🗄️ Configuração do Banco de Dados](#️-configuração-do-banco-de-dados)
- [🌐 API Endpoints](#-api-endpoints)
- [🖥️ Frontend](#️-frontend)
- [🔐 Segurança](#-segurança)
- [💾 Backup e Restauração](#-backup-e-restauração)
- [📝 Scripts Utilitários](#-scripts-utilitários)
- [👥 Créditos](#-créditos)
- [📞 Suporte](#-suporte)
- [📊 Estatísticas do Projeto](#-estatísticas-do-projeto)
- [🚀 Roadmap](#-roadmap)

---

## 🎯 Visão Geral

O **Consorti API** é um sistema de gestão completo desenvolvido para atender às necessidades de controle de estoque, vendas e relatórios de uma empresa de equipamentos locais.

### Principais Características:

- ✅ **100% Local** - Roda em servidor local via XAMPP, sem dependência de internet
- ✅ **Multi-usuário** - Perfis diferenciados (Admin, Vendedor, Cliente)
- ✅ **Tempo Real** - Estoque atualizado instantaneamente após cada venda
- ✅ **Relatórios Gerenciais** - 8 tipos de relatórios com lucro, descontos e métricas
- ✅ **PDV Integrado** - Ponto de venda com cálculo de lucro e descontos
- ✅ **Dados Padronizados** - +2.100 produtos importados e organizados

---

## 🚀 Funcionalidades

### 📦 Gestão de Produtos
- Cadastro de +2.100 produtos com SKU único
- Organização por marcas (104+ marcas)
- Preços de custo e venda editáveis
- Cálculo automático de margem de lucro
- Controle de estoque em tempo real

### 🛒 Ponto de Venda (PDV)
- Busca rápida de produtos (nome, SKU)
- Carrinho de compras com múltiplos itens
- Descontos em R$ ou porcentagem
- Cálculo de lucro em tempo real
- Baixa automática de estoque
- Histórico de vendas por usuário

### 📥 Entrada de Estoque
- Registro de compras de fornecedores
- Ajustes de estoque
- Devoluções de clientes
- Atualização automática de custos

### 📊 Relatórios Gerenciais

| Relatório | Descrição |
|-----------|-----------|
| Dashboard Geral | Visão completa com KPIs principais |
| Estoque Baixo | Produtos críticos para reposição |
| Mais Vendidos | Top produtos por quantidade e lucro |
| Resumo Estoque | Valores totais e lucro potencial |
| Vendas por Período | Evolução temporal das vendas |
| Receita e Lucro | Resultados financeiros detalhados |
| Ranking de Lucro | Produtos mais lucrativos |
| Descontos | Controle de descontos concedidos |

### 👥 Gestão de Usuários
- CRUD completo de usuários
- Níveis de acesso (Admin, Vendedor, Cliente)
- Autenticação segura com `password_hash`
- Rastreamento de vendas por vendedor

---

## 🛠️ Tecnologias

### Backend

| Tecnologia | Versão | Descrição |
|-----------|--------|-----------|
| PHP | 8.0+ | Linguagem principal do backend |
| MySQL | 8.0+ | Banco de dados relacional |
| PDO | - | Camada de acesso a dados com prepared statements |
| Apache | 2.4+ | Servidor web (via XAMPP) |

### Frontend

| Tecnologia | Versão | Descrição |
|-----------|--------|-----------|
| HTML5 | - | Estrutura das páginas |
| CSS3 | - | Estilização customizada |
| JavaScript | ES6+ | Lógica do frontend (Vanilla) |
| Bootstrap | 5.3 | Framework CSS responsivo |
| Bootstrap Icons | 1.11 | Ícones da interface |

### Ferramentas

| Ferramenta | Descrição |
|-----------|-----------|
| XAMPP | Ambiente de desenvolvimento local |
| phpMyAdmin | Gerenciamento do banco de dados |
| Postman | Testes de API |
| Git | Controle de versão |

---

## 📁 Estrutura do Projeto

```
consorti_api/
├── 📁 app/                          # Frontend (Aplicação Web)
│   ├── index.html                   # Tela de login
│   ├── dashboard.html               # Dashboard principal
│   ├── estoque.html                 # Consulta de estoque
│   ├── pdv.html                     # Ponto de venda
│   ├── entrada.html                 # Entrada de estoque
│   ├── relatorios.html              # Relatórios gerenciais
│   ├── vendedores.html              # Gestão de usuários
│   ├── config.html                  # Configurações
│   └── assets/
│       ├── css/
│       │   └── style.css            # Estilos globais
│       └── js/
│           ├── api.js               # Conexão com API
│           ├── auth.js              # Autenticação
│           └── app.js               # Lógica geral
│
├── 📁 config/
│   └── db.php                       # Configuração do banco de dados
│
├── 📁 controllers/
│   ├── ProdutosController.php       # Controller de produtos
│   ├── UsuariosController.php       # Controller de usuários
│   ├── MovimentacoesController.php  # Controller de movimentações
│   └── RelatoriosController.php     # Controller de relatórios
│
├── 📁 models/
│   ├── Produto.php                  # Model de produto
│   ├── Usuario.php                  # Model de usuário
│   ├── Movimentacao.php             # Model de movimentação
│   └── Relatorio.php                # Model de relatórios
│
├── 📁 routes/
│   ├── produtos.php                 # Rotas de produtos
│   ├── usuarios.php                 # Rotas de usuários
│   ├── movimentacoes.php            # Rotas de movimentações
│   ├── relatorios.php               # Rotas de relatórios
│   └── marcas.php                   # Rotas de marcas
│
├── 📁 scripts/
│   ├── importar_csv.php             # Script de importação de produtos
│   ├── converter_utf8.php           # Conversor de encoding CSV
│   ├── validar_produtos.php         # Validador de dados
│   └── gerar_senha.php              # Gerador de hash de senha
│
├── index.php                        # Router principal da API
├── .htaccess                        # Configurações do Apache
└── README.md                        # Este arquivo
```

---

## 📦 Instalação

### Pré-requisitos

- Windows 10/11 ou Windows Server
- XAMPP 8.0+ (Apache + MySQL + PHP)
- Navegador moderno (Chrome, Firefox, Edge)

### Passo a Passo

#### 1. Instalar XAMPP

```bash
# Download: https://www.apachefriends.org/
# Instalar em: C:\xampp
# Iniciar: Apache + MySQL
```

#### 2. Copiar o Projeto

```bash
# Copiar pasta consorti_api para:
C:\xampp\htdocs\consorti_api\
```

#### 3. Configurar Banco de Dados

```sql
-- Acessar: http://localhost/phpmyadmin

-- Criar banco
CREATE DATABASE consorti_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Importar backup
-- Selecionar consorti_db → Importar → backup_consorti_YYYY-MM-DD.sql
```

#### 4. Configurar Conexão

Editar `config/db.php`:

```php
<?php
class DB {
    private $host = 'localhost';
    private $db   = 'consorti_db';
    private $user = 'root';
    private $pass = ''; // Alterar se configurou senha no MySQL
    private $charset = 'utf8mb4';
    // ...
}
```

#### 5. Acessar o Sistema

```
🌐 URL: http://localhost/consorti_api/app/

🔐 Credenciais Padrão:
   E-mail: admin@consorti.com.br
   Senha: 123
```

#### 6. Configurar Acesso pela Rede (Opcional)

Para acessar de outros PCs na rede local:

1.  Editar `C:\xampp\apache\conf\extra\httpd-xampp.conf`
2.  Substituir `Require local` por `Require all granted`
3.  Descobrir IP do servidor: `ipconfig` no CMD
4.  Acessar de outros PCs: `http://[IP-DO-SERVIDOR]/consorti_api/app/`

---

## 🗄️ Configuração do Banco de Dados

### Tabelas Principais

#### `marcas`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| nome | VARCHAR(100) | Nome da marca |
| criado_em | TIMESTAMP | Data de cadastro |

#### `produtos`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| marca_id | INT | Chave estrangeira (marcas) |
| nome | VARCHAR(255) | Nome do produto |
| sku | VARCHAR(100) | SKU único |
| estoque | INT | Quantidade em estoque |
| preco_custo | DECIMAL(10,2) | Preço de custo |
| preco_venda | DECIMAL(10,2) | Preço de venda |
| ativo | TINYINT(1) | Status (1=ativo, 0=inativo) |
| criado_em | TIMESTAMP | Data de cadastro |
| atualizado_em | TIMESTAMP | Última atualização |

#### `usuarios`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| nome | VARCHAR(100) | Nome completo |
| email | VARCHAR(100) | E-mail (login) |
| senha | VARCHAR(255) | Senha (bcrypt) |
| nivel | ENUM | admin, vendedor, cliente |
| ativo | TINYINT(1) | Status |

#### `movimentacoes`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária |
| produto_id | INT | Chave estrangeira (produtos) |
| usuario_id | INT | Chave estrangeira (usuarios) |
| tipo | ENUM | entrada, saida, ajuste, pedido |
| quantidade | INT | Quantidade movimentada |
| desconto | DECIMAL(10,2) | Desconto aplicado |
| lucro | DECIMAL(10,2) | Lucro da operação |
| total_venda | DECIMAL(10,2) | Total da venda |
| observacao | VARCHAR(255) | Observações |
| origem | VARCHAR(50) | Origem (pdv, entrada, etc.) |
| criado_em | TIMESTAMP | Data da movimentação |

---

## 🌐 API Endpoints

### Base URL
```
http://localhost/consorti_api/
```

### Autenticação
```http
POST /usuarios?acao=login
Content-Type: application/json

{
  "email": "admin@consorti.com.br",
  "senha": "123"
}
```

### Produtos

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/produtos` | Listar produtos (com paginação) |
| GET | `/produtos?id={id}` | Buscar produto por ID |
| GET | `/produtos?busca={termo}` | Buscar por nome/SKU |
| GET | `/produtos?marca_id={id}` | Filtrar por marca |
| PUT | `/produtos?id={id}` | Atualizar preços |

### Marcas

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/marcas` | Listar todas as marcas |

### Movimentações

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/movimentacoes` | Listar movimentações |
| GET | `/movimentacoes?id={id}` | Buscar por ID |
| POST | `/movimentacoes` | Criar movimentação |
| GET | `/movimentacoes?resumo_produto={id}` | Resumo por produto |

### Relatórios

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/relatorios?tipo=dashboard` | Dashboard geral |
| GET | `/relatorios?tipo=estoque_baixo` | Estoque baixo |
| GET | `/relatorios?tipo=produtos_mais_vendidos` | Mais vendidos |
| GET | `/relatorios?tipo=resumo_estoque` | Resumo de estoque |
| GET | `/relatorios?tipo=vendas_periodo` | Vendas por período |
| GET | `/relatorios?tipo=receita_periodo` | Receita e lucro |
| GET | `/relatorios?tipo=ranking_lucro` | Ranking de lucro |
| GET | `/relatorios?tipo=descontos` | Descontos concedidos |

### Usuários

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/usuarios` | Listar usuários |
| GET | `/usuarios?id={id}` | Buscar por ID |
| POST | `/usuarios` | Criar usuário |
| PUT | `/usuarios?id={id}` | Atualizar usuário |
| DELETE | `/usuarios?id={id}` | Excluir usuário |

---

## 🖥️ Frontend

### Telas Disponíveis

| Tela | URL | Descrição |
|------|-----|-----------|
| Login | `/app/` | Autenticação de usuários |
| Dashboard | `/app/dashboard.html` | Visão geral do sistema |
| Estoque | `/app/estoque.html` | Consulta e edição de produtos |
| PDV | `/app/pdv.html` | Ponto de venda |
| Entrada | `/app/entrada.html` | Registro de entrada de estoque |
| Relatórios | `/app/relatorios.html` | Relatórios gerenciais |
| Vendedores | `/app/vendedores.html` | Gestão de usuários |
| Config | `/app/config.html` | Configurações do sistema |

### Acessando pela Rede Local

```
# No servidor:
http://localhost/consorti_api/app/

# Em outros PCs na rede:
http://192.168.0.100/consorti_api/app/
(Substituir pelo IP do servidor)
```

---

## 🔐 Segurança

### Boas Práticas Implementadas

- ✅ Senhas criptografadas com `password_hash()` (bcrypt)
- ✅ Prepared statements (proteção contra SQL Injection)
- ✅ Headers CORS configurados
- ✅ Validação de entrada de dados
- ✅ Níveis de acesso diferenciados
- ✅ Sessão com LocalStorage (uso local seguro)

### Recomendações para Produção

```sql
-- 1. Alterar senha do root do MySQL
ALTER USER 'root'@'localhost' IDENTIFIED BY 'senha_forte';

-- 2. Atualizar config/db.php com a nova senha

-- 3. Liberar porta 80 apenas para rede local no Firewall
netsh advfirewall firewall add rule name="Apache HTTP" dir=in action=allow protocol=TCP localport=80
```

---

## 💾 Backup e Restauração

### Backup do Banco de Dados

```bash
# Via phpMyAdmin:
# 1. Acessar http://localhost/phpmyadmin
# 2. Selecionar banco "consorti_db"
# 3. Clicar em "Exportar"
# 4. Formato: SQL
# 5. Salvar arquivo

# Via linha de comando:
mysqldump -u root -p consorti_db > backup_$(date +%Y-%m-%d).sql
```

### Restauração

```bash
# Via phpMyAdmin:
# 1. Acessar http://localhost/phpmyadmin
# 2. Selecionar banco "consorti_db"
# 3. Clicar em "Importar"
# 4. Selecionar arquivo SQL
# 5. Executar

# Via linha de comando:
mysql -u root -p consorti_db < backup_YYYY-MM-DD.sql
```

### Backup Automatizado (Windows Task Scheduler)

```batch
@echo off
set BACKUP_DIR=C:\xampp\htdocs\consorti_api\backups
set DATE=%date:~-4,4%%date:~-7,2%%date:~-10,2%
mysqldump -u root -pSENHA consorti_db > %BACKUP_DIR%\backup_%DATE%.sql
```

---

## 📝 Scripts Utilitários

### Importar CSV de Produtos

```
# Acessar:
http://localhost/consorti_api/scripts/importar_csv.php

# Pré-requisitos:
- Arquivo CSV em UTF-8 sem BOM
- Colunas: MARCA, PRODUTO, Estoque, PREÇO UNID.
- Separador: TABULAÇÃO
```

### Converter Encoding CSV

```
# Acessar:
http://localhost/consorti_api/scripts/converter_utf8.php

# Converte CSV de ISO-8859-1 para UTF-8 sem BOM
```

### Gerar Hash de Senha

```
# Acessar:
http://localhost/consorti_api/scripts/gerar_senha.php

# Gera hash bcrypt para uso no banco de dados
```

---

## 👥 Créditos

### Desenvolvimento

| Função | Responsável | Contato |
|--------|-----------|-----------
| Backend API | Isaias Lourenço | contato@vetor256.com
| Frontend | Isaias Lourenço | contato@vetor256.com
| Banco de Dados | Isaias Lourenço | contato@vetor256.com
| Importação de Dados | Isaias Lourenço | contato@vetor256.com

### Cliente

- **Empresa:** Consorti Equipamentos
- **Responsável:** Sr. Nelson Consorti
- **Local:** Mogi Guaçu/SP

### Licença

```
⚠️ PROPRIETÁRIO

Este software foi desenvolvido sob encomenda pela empresa Vetor256. para Consorti Equipamentos.
Todos os direitos reservados.

É proibida a cópia, distribuição ou uso comercial sem autorização expressa.
```

---

## 📞 Suporte

| Canal | Contato |
|-------|---------|
| E-mail | contato@vetor256.com |
| Telefone | (19) 97111-0538 |
| Site | https://vetor256.com |
| Período | 30 dias inclusos |

---

## 📊 Estatísticas do Projeto

| Métrica | Valor |
|---------|-------|
| Produtos Cadastrados | 2.100+ |
| Marcas | 104+ |
| Linhas de Código (Backend) | ~3.500 |
| Linhas de Código (Frontend) | ~4.000 |
| Endpoints da API | 25+ |
| Telas do Frontend | 8 |
| Relatórios | 8 |

---

## 🚀 Roadmap (Futuro)

- [ ] Integração com site e-commerce (sync diário)
- [ ] Exportação de relatórios em PDF/Excel
- [ ] Notificações de estoque baixo por e-mail
- [ ] Backup automático em nuvem
- [ ] App mobile nativo (Android/iOS)
- [ ] Emissão de NF-e integrada
- [ ] Controle financeiro (contas a pagar/receber)
- [ ] Múltiplos estoques (filiais)

---

## 🙏 Agradecimentos

Obrigado a todos que contribuíram para este projeto!

**Desenvolvido pela [Vetor256.](https://vetor256.com) com ❤️ para Consorti Equipamentos**

---

<div align="center">

**© 2026 [Vetor256.](https://vetor256.com) - Todos os direitos reservados**

</div>