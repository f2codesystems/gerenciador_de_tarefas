-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Tempo de geração: 29/06/2025 às 18:16
-- Versão do servidor: 10.4.28-MariaDB
-- Versão do PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `todolist`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'Pessoal'),
(2, 'Trabalho'),
(3, 'Estudos');

-- --------------------------------------------------------

--
-- Estrutura para tabela `notificacoes`
--

CREATE TABLE `notificacoes` (
  `id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `data` datetime NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tarefas`
--

CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `prazo_inicial` datetime DEFAULT NULL,
  `prazo_final` datetime DEFAULT NULL,
  `prazo` date DEFAULT NULL,
  `prioridade` enum('Baixa','Média','Alta') DEFAULT NULL,
  `concluida` tinyint(1) DEFAULT 0,
  `categoria_id` int(11) DEFAULT NULL,
  `status` enum('todo','inprogress','done') DEFAULT 'todo',
  `usuario_id` int(11) DEFAULT NULL,
  `ordem` int(11) DEFAULT 0,
  `evento_google_id` varchar(255) DEFAULT NULL,
  `event_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tarefas`
--

INSERT INTO `tarefas` (`id`, `titulo`, `descricao`, `prazo_inicial`, `prazo_final`, `prazo`, `prioridade`, `concluida`, `categoria_id`, `status`, `usuario_id`, `ordem`, `evento_google_id`, `event_id`) VALUES
(100, 'TAREFA SISTEMA 1', 'TESTE SISTEMA 1', '2025-06-28 20:52:00', '2025-06-29 20:52:00', NULL, 'Baixa', 0, 3, 'todo', 19, 0, NULL, NULL),
(101, 'TAREFA SISTEMA 2', 'TESTE SISTEMA 2', '2025-06-28 20:53:00', '2025-07-05 20:53:00', NULL, 'Média', 0, 1, 'inprogress', 19, 0, NULL, NULL),
(102, 'TAREFA SISTEMA 3', 'TESTE SISTEMA 3', '2025-06-28 20:54:00', '2025-06-30 20:54:00', NULL, 'Alta', 0, 2, 'done', 19, 0, NULL, NULL),
(110, 'TAREFA 1 GOOGLE', '', '2025-06-29 17:00:00', '2025-06-29 18:00:00', NULL, 'Média', 0, NULL, 'todo', 14, 0, NULL, '2khgi31c2vos4kkkuu2phsiksc'),
(111, 'TAREFA 2 GOOGLE', '', '2025-06-29 17:00:00', '2025-06-29 18:00:00', NULL, 'Média', 0, NULL, 'inprogress', 14, 0, NULL, '4tcgee5ialmsch1nit6sj73tn4'),
(112, 'TAREFA 3 GOOGLE', '', '2025-06-29 17:00:00', '2025-06-29 18:00:00', NULL, 'Média', 0, NULL, 'done', 14, 0, NULL, '5qti5m1t58e6m4t17n1sb8etl7'),
(113, 'TAREFA 4 SISTEMA', 'TAREFA 4 SISTEMA', '2025-06-29 16:42:00', '2025-06-30 16:42:00', NULL, 'Baixa', 0, 3, 'todo', 14, 0, NULL, 'li9vm01bq0osknaqqatbt6sevg'),
(114, 'TAREFA 5 GOOGLE', '', '2025-06-29 17:00:00', '2025-06-29 18:00:00', NULL, 'Média', 0, NULL, 'inprogress', 14, 0, NULL, '23r4vtnn8ftopib5f5blla08kg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `telefone` varchar(30) DEFAULT NULL,
  `nivel` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `foto`, `email`, `senha`, `telefone`, `nivel`) VALUES
(14, 'Felipe | User Google', 'foto_1751066984.png', 'usef2code@gmail.com', '$2y$10$Tk88vOd8Mu1KH5vqCZp4qOkR4X7cv/AAfUVttP8hRAp66JK.oe4s2', '+5531991902080', 'administrador'),
(19, 'Felipe | User Sistema', '685ed83f9ba27.png', 'felipe.2c@hotmail.com', '$2y$10$rReR7fix28NJuGnIx30PD.qGEIeQuLcdITblfP/S05zI0lYLsUVGe', '+5531991902080', 'usuario');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `tarefas`
--
ALTER TABLE `tarefas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `fk_usuario` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `notificacoes`
--
ALTER TABLE `notificacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `tarefas`
--
ALTER TABLE `tarefas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `notificacoes`
--
ALTER TABLE `notificacoes`
  ADD CONSTRAINT `notificacoes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `tarefas`
--
ALTER TABLE `tarefas`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `tarefas_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
