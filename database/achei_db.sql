-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 02/11/2026 às 03:26
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `achei_db`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `fotos_objetos`
--

CREATE TABLE `fotos_objetos` (
  `id` int(11) NOT NULL,
  `objeto_id` int(11) NOT NULL,
  `foto_numero` tinyint(4) NOT NULL,
  `caminho` varchar(255) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Despejando dados para a tabela `fotos_objetos`
--

INSERT INTO `fotos_objetos` (`id`, `objeto_id`, `foto_numero`, `caminho`, `criado_em`) VALUES
(1, 4, 1, 'assets/img/objetos/63356efd368d923c60b452a3844ceff9.webp', '2026-11-01 21:56:14'),
(2, 5, 1, 'assets/img/objetos/ae651a283992e676e63ad7215abd55d9.png', '2026-11-01 21:57:06'),
(3, 6, 1, 'assets/img/objetos/c6d9bcccae1021e66ce1206c5054cf19.jpg', '2026-11-01 22:49:28'),
(4, 6, 2, 'assets/img/objetos/28f1591a5b712a8574175cec87706e8c.png', '2026-11-01 22:49:28'),
(5, 6, 3, 'assets/img/objetos/21ebf3d4e3c56244bc173c5e5c845dd8.webp', '2026-11-01 22:49:28'),
(6, 7, 1, 'assets/img/objetos/cc970dfddbe1057ddee2c026768d8e84.jpg', '2026-11-02 00:44:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `objetos`
--

CREATE TABLE `objetos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `polo_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` text NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `data_ocorrencia` date NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `status` enum('disponivel','retirado') NOT NULL DEFAULT 'disponivel',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `objetos`
--

INSERT INTO `objetos` (`id`, `usuario_id`, `polo_id`, `titulo`, `descricao`, `categoria`, `data_ocorrencia`, `imagem`, `status`, `criado_em`) VALUES
(1, 1, 1, 'Garrafa térmica azul', 'Garrafa térmica azul encontrada na biblioteca.', 'Acessórios', '2026-11-01', NULL, 'disponivel', '2026-11-01 21:33:13'),
(2, 1, 1, 'Caderno vermelho de teste', 'Caderno vermelho encontrado na sala de aula.', 'Material escolar', '2026-11-01', NULL, 'disponivel', '2026-11-01 21:53:50'),
(3, 1, 1, 'Jaqueta de Couro', 'Encontrada na sala 11', 'Roupas', '2026-10-02', NULL, 'disponivel', '2026-11-01 21:54:43'),
(4, 1, 1, 'Celular', 'Encontrada na sala 9', 'Eletrônicos', '2026-10-02', NULL, 'disponivel', '2026-11-01 21:56:14'),
(5, 1, 1, 'notebook', 'sala 4', 'Eletrônicos', '2026-05-10', NULL, 'disponivel', '2026-11-01 21:57:06'),
(6, 1, 1, 'Aleatorio', 'varios objetos aleatorios', 'Outros', '2026-10-06', NULL, 'disponivel', '2026-11-01 22:49:28'),
(7, 4, 2, 'Calculadora', 'Encontrada na sala de descanso', 'Eletrônicos', '2026-09-20', NULL, 'disponivel', '2026-11-02 00:44:07');

-- --------------------------------------------------------

--
-- Estrutura para tabela `polos`
--

CREATE TABLE `polos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cep` varchar(9) NOT NULL,
  `logradouro` varchar(150) NOT NULL,
  `bairro` varchar(100) NOT NULL,
  `cidade` varchar(100) NOT NULL,
  `estado` char(2) NOT NULL,
  `numero` varchar(20) NOT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `contato` varchar(100) NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `polos`
--

INSERT INTO `polos` (`id`, `nome`, `cep`, `logradouro`, `bairro`, `cidade`, `estado`, `numero`, `complemento`, `contato`, `criado_em`) VALUES
(1, 'Polo de Teste', '12245000', 'Avenida de Teste', 'Centro', 'São José dos Campos', 'SP', '100', NULL, 'contato@teste.com', '2026-11-01 21:32:22'),
(2, 'Polo SJC', '12236230', 'Rua Santo Inácio de Loyola', 'Jardim Oriente', 'São José dos Campos', 'SP', '392', 'polo', '12) 99673-5465', '2026-11-02 00:05:51');

-- --------------------------------------------------------

--
-- Estrutura para tabela `solicitacoes`
--

CREATE TABLE `solicitacoes` (
  `id` int(11) NOT NULL,
  `objeto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `mensagem` text NOT NULL,
  `status` enum('pendente','aceita','recusada','concluida') NOT NULL DEFAULT 'pendente',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('aluno','funcionario','admin') NOT NULL DEFAULT 'aluno',
  `polo_id` int(11) DEFAULT NULL,
  `provisionado_por` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `polo_id`, `provisionado_por`, `criado_em`) VALUES
(1, 'Funcionário de Teste', 'funcionario@funcionario.unisa.br', '$2y$10$ATwTG.9Kj7WGHDBbOW8H1euRjPJcSQavbodI8y4QRmYQGRHawtGqW', 'funcionario', 1, 2, '2026-11-01 21:32:41'),
(2, 'Administrador', 'admin123@achados.com', '$2y$10$XCYR7OIcc/89bLggLWRFLeN79Ktbd4/Ko7Wj3oHeqmbDsuRLYoy72', 'admin', NULL, NULL, '2026-11-01 23:39:02'),
(3, 'Matheus', 'matheusde14@estudante.unisa.br', '$2y$10$gS7LcJmzxdyOIqKAmQJ64eXR/ysYoJPIUFz7A97F1dm/rVNggLaPa', 'aluno', NULL, NULL, '2026-11-01 23:59:38'),
(4, 'João souza', 'joao123@funcionario.unisa.br', '$2y$10$LhAZodQVl0Ec/09U2izo2eWvwQcz5RNwhMUiOv7W.Xd5mkAXxsEfG', 'funcionario', 2, 2, '2026-11-02 00:05:51');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `fotos_objetos`
--
ALTER TABLE `fotos_objetos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_foto_objeto_numero` (`objeto_id`,`foto_numero`);

--
-- Índices de tabela `objetos`
--
ALTER TABLE `objetos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_objetos_polos` (`polo_id`);

--
-- Índices de tabela `polos`
--
ALTER TABLE `polos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `solicitacoes`
--
ALTER TABLE `solicitacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `objeto_id` (`objeto_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_usuarios_polos` (`polo_id`),
  ADD KEY `idx_usuarios_provisionado_por` (`provisionado_por`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `fotos_objetos`
--
ALTER TABLE `fotos_objetos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `objetos`
--
ALTER TABLE `objetos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `polos`
--
ALTER TABLE `polos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `solicitacoes`
--
ALTER TABLE `solicitacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `fotos_objetos`
--
ALTER TABLE `fotos_objetos`
  ADD CONSTRAINT `fk_fotos_objetos` FOREIGN KEY (`objeto_id`) REFERENCES `objetos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Restrições para tabelas `objetos`
--
ALTER TABLE `objetos`
  ADD CONSTRAINT `fk_objetos_polos` FOREIGN KEY (`polo_id`) REFERENCES `polos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `objetos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `solicitacoes`
--
ALTER TABLE `solicitacoes`
  ADD CONSTRAINT `solicitacoes_ibfk_1` FOREIGN KEY (`objeto_id`) REFERENCES `objetos` (`id`),
  ADD CONSTRAINT `solicitacoes_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_polos` FOREIGN KEY (`polo_id`) REFERENCES `polos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
