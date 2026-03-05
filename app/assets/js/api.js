/**
 * API.js - Conexão com a API Consorti
 * Todas as requisições passam por aqui
 */

const API_BASE = '/consorti_api';

const API = {
    /**
     * Faz requisição para a API
     * @param {string} endpoint - Endpoint da API (ex: '/usuarios?acao=login')
     * @param {string} method - Método HTTP (GET, POST, PUT, DELETE)
     * @param {object} data - Dados para enviar (para POST/PUT)
     * @returns {Promise} - Resposta da API
     */
    async request(endpoint, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data && method !== 'GET') {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${API_BASE}${endpoint}`, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.mensagem || 'Erro na requisição');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    // === AUTENTICAÇÃO ===
    login: (email, senha) => API.request('/usuarios?acao=login', 'POST', { email, senha }),
    
    // === PRODUTOS ===
    listarProdutos: (params = {}) => {
        const query = new URLSearchParams(params).toString();
        return API.request(`/produtos${query ? '?' + query : ''}`);
    },
    buscarProduto: (id) => API.request(`/produtos?id=${id}`),
    buscarProdutosPorMarca: (marca_id) => API.request(`/produtos?marca_id=${marca_id}`),
    buscarProdutosPorBusca: (busca) => API.request(`/produtos?busca=${encodeURIComponent(busca)}`),
    
    // ✅ NOVO: Atualizar produto (preços)
    atualizarProduto: (id, data) => API.request(`/produtos?id=${id}`, 'PUT', data),

    // === MARCAS ===
    listarMarcas: () => API.request('/marcas'),

    // === MOVIMENTAÇÕES ===
    criarMovimentacao: (data) => API.request('/movimentacoes', 'POST', data),
    listarMovimentacoes: (params = {}) => {
        const query = new URLSearchParams(params).toString();
        return API.request(`/movimentacoes${query ? '?' + query : ''}`);
    },
    resumoMovimentacoes: (produto_id) => API.request(`/movimentacoes?resumo_produto=${produto_id}`),

    // === RELATÓRIOS ===
    relatorio: (tipo, params = {}) => {
        const query = new URLSearchParams({ tipo, ...params }).toString();
        return API.request(`/relatorios?${query}`);
    },

    // === USUÁRIOS ===
    listarUsuarios: (params = {}) => {
        const query = new URLSearchParams(params).toString();
        return API.request(`/usuarios${query ? '?' + query : ''}`);
    },
    buscarUsuario: (id) => API.request(`/usuarios?id=${id}`),
    criarUsuario: (data) => API.request('/usuarios', 'POST', data),
    atualizarUsuario: (id, data) => API.request(`/usuarios?id=${id}`, 'PUT', data),
    excluirUsuario: (id) => API.request(`/usuarios?id=${id}`, 'DELETE'),
};