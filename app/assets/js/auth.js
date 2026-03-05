/**
 * Auth.js - Gerenciamento de Autenticação e Sessão
 */

const Auth = {
    STORAGE_KEY: 'consorti_user',

    /**
     * Faz login e salva sessão
     */
    async login(email, senha) {
        try {
            const response = await API.login(email, senha);
            
            if (response.status === 'sucesso') {
                // Salva dados do usuário no localStorage
                const user = {
                    id: response.dados.id,
                    nome: response.dados.nome,
                    email: response.dados.email,
                    nivel: response.dados.nivel,
                    login_em: new Date().toISOString()
                };
                
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(user));
                return { success: true, user };
            } else {
                return { success: false, error: response.mensagem };
            }
        } catch (error) {
            return { success: false, error: error.message };
        }
    },

    /**
     * Verifica se está logado
     */
    estaLogado() {
        return this.getUsuario() !== null;
    },

    /**
     * Retorna dados do usuário logado
     */
    getUsuario() {
        const user = localStorage.getItem(this.STORAGE_KEY);
        return user ? JSON.parse(user) : null;
    },

    /**
     * Retorna nível do usuário
     */
    getNivel() {
        const user = this.getUsuario();
        return user ? user.nivel : null;
    },

    /**
     * Verifica se tem permissão
     */
    temPermissao(niveisPermitidos) {
        const nivel = this.getNivel();
        if (!nivel) return false;
        
        if (Array.isArray(niveisPermitidos)) {
            return niveisPermitidos.includes(nivel);
        }
        return nivel === niveisPermitidos;
    },

    /**
     * Faz logout
     */
    logout() {
        localStorage.removeItem(this.STORAGE_KEY);
        window.location.href = 'index.html';
    },

    /**
     * Redireciona se não estiver logado
     */
    requerLogin() {
        if (!this.estaLogado()) {
            window.location.href = 'index.html';
            return false;
        }
        return true;
    },

    /**
     * Atualiza dados do usuário na sessão
     */
    atualizarSessao(novosDados) {
        const user = this.getUsuario();
        if (user) {
            Object.assign(user, novosDados);
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(user));
        }
    }
};

// === Event Listener para o formulário de login ===
document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('formLogin');
    const alerta = document.getElementById('alerta');

    if (formLogin) {
        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const lembrar = document.getElementById('lembrar').checked;

            // Feedback visual
            const btn = formLogin.querySelector('button[type="submit"]');
            const textoOriginal = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Entrando...';

            // Tenta login
            const result = await Auth.login(email, senha);

            if (result.success) {
                // Sucesso
                if (lembrar) {
                    localStorage.setItem('consorti_lembrar', 'true');
                }
                
                // Redireciona para dashboard
                window.location.href = 'dashboard.html';
            } else {
                // Erro
                alerta.textContent = result.error || 'E-mail ou senha inválidos';
                alerta.classList.remove('d-none');
                
                // Restaura botão
                btn.disabled = false;
                btn.innerHTML = textoOriginal;
            }
        });
    }
});