📌 Gerenciador de Tarefas

🚀 Gerenciador de Tarefas é um sistema web desenvolvido como parte do meu Trabalho de Conclusão de Curso (TCC), com foco em organização, produtividade e usabilidade. Ele permite o gerenciamento de atividades através de uma interface estilo Kanban, relatórios gerenciais e integração com a API do Google Calendar.

⚙️ Funcionalidades

✅ Cadastro, edição, exclusão e conclusão de tarefas

✅ Organização visual estilo Kanban (A Fazer, Em Progresso, Concluídas)

✅ Notificações de prazos vencidos ou próximos

✅ Filtros dinâmicos por status

✅ Relatórios

✅ Integração com Google Calendar API

🛠️ Tecnologias Utilizadas

Frontend: HTML5, CSS3, JavaScript, Bootstrap

Backend: PHP 7.4+

Banco de Dados: MySQL

Gerenciador de dependências: Composer

Integrações: Google Calendar API

Versionamento: Git/GitHub

📂 Estrutura do Projeto
gerenciador_de_tarefas/
├── src/                # Código-fonte (PHP, HTML, CSS, JS)
├── public/             # Arquivos acessíveis pelo navegador
├── database/           # Scripts de criação e estrutura do banco
├── vendor/             # Dependências instaladas via Composer
├── .env.example        # Exemplo de configuração de variáveis de ambiente
├── composer.json       # Dependências do PHP
└── README.md           # Documentação do projeto

📋 Pré-requisitos

Antes de rodar o projeto, certifique-se de ter instalado:

XAMPP ou similar (PHP 7.4+, Apache, MySQL)

Composer
 (para gerenciar dependências)

Navegador atualizado (Chrome, Firefox, Edge etc.)

🚀 Instalação e Execução Local

Clonar o repositório:

git clone https://github.com/f2codesystems/gerenciador_de_tarefas.git
cd gerenciador_de_tarefas


Instalar dependências via Composer:

composer install


Configurar o ambiente:

Copie o arquivo .env.example e renomeie para .env

Preencha com suas credenciais locais de banco de dados e da API Google

Configurar o banco de dados:

Acesse o phpMyAdmin

Crie um banco de dados, por exemplo: gerenciador_tarefas

Importe o arquivo .sql disponível na pasta database/

Rodar o projeto no servidor local (XAMPP):

Coloque o projeto dentro da pasta htdocs/

Inicie Apache e MySQL no XAMPP

Acesse no navegador:

http://localhost/gerenciador_de_tarefas

🔗 Repositório

📌 GitHub: https://github.com/f2codesystems/gerenciador_de_tarefas

👨‍💻 Autor

Felipe Costa Correa
💼 LinkedIn: Felipe Correa - https://www.linkedin.com/in/felipecostacorrea/

📜 Licença

Este projeto foi desenvolvido para fins acadêmicos e está disponível para consulta pública.
