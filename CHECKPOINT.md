CHECKPOINT — PROJETO ACHEI!
1. Sobre o projeto
Nome: Achei!
Objetivo: desenvolver um catálogo interno de objetos encontrados nos polos da instituição.

Tecnologias:
- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP
- phpMyAdmin
Pasta do projeto:

C:\xampp\htdocs\Achei\

O wireframe foi enviado e analisado. O desenvolvimento visual já começou e a página inicial e o menu foram implementados.

2. Funcionamento confirmado
Aluno
- Acessa o sistema com login.
- Utiliza e-mail institucional de estudante.
- Pode pesquisar e visualizar objetos encontrados.
- Pode combinar filtros de pesquisa.
- Pode visualizar detalhes do objeto, polo onde está guardado e contato da coordenação.
- Não pode cadastrar objetos.
- Não envia solicitações de devolução pelo sistema.
Funcionário
- Acessa o sistema com login.
- Utiliza e-mail institucional de funcionário.
- Pode cadastrar objetos encontrados.
- Pode gerenciar os objetos do polo ao qual está vinculado.
- Informa o polo onde trabalha durante o cadastro.
- Cada funcionário fica vinculado a apenas um polo.
- Cada objeto pode ter até 3 fotos.
Administrador geral
- Pode gerenciar os dados gerais do sistema.
- A definição completa das permissões administrativas ainda está pendente.

3. Identificação pelo e-mail
Domínios definidos para a demonstração acadêmica:
Domínio	Perfil
@estudante.unisa.br	aluno
@funcionario.unisa.br	funcionario

O domínio @funcionario.unisa.br é fictício e será usado apenas no projeto acadêmico. Não foi confirmado como domínio real da instituição.

O cadastro público cria somente alunos com e-mail `@estudante.unisa.br`. Contas de funcionário e administrador são provisionadas pelo administrador; o domínio fictício não concede privilégios.

4. Cadastro de polos
O funcionário informa onde trabalha.
- Se o polo já existir, a conta será vinculada a ele.
- Se o polo ainda não existir, poderá ser cadastrado durante o fluxo.
- O funcionário não precisa escolher o próprio perfil.
- O funcionário não poderá gerenciar objetos de outros polos.
Preenchimento do endereço
O CEP será consultado automaticamente pelo ViaCEP.

O endereço será preenchido automaticamente, e o funcionário poderá completar:
- Número
- Complemento
- Nome do polo
- Contato da coordenação

5. Banco de dados
Banco: achei_db
Tabela usuarios
Campos:
- id
- nome
- email
- senha
- perfil
- polo_id
- criado_em
Perfis permitidos:
- aluno
- funcionario
- admin
polo_id é opcional para alunos e administradores. Funcionários deverão ter um polo vinculado.

Relacionamento confirmado:

usuarios.polo_id → polos.id
Tabela polos
Campos:
- id
- nome
- cep
- logradouro
- bairro
- cidade
- estado
- numero
- complemento
- contato
- criado_em
Tabela objetos
Campos:
- id
- usuario_id
- polo_id
- titulo
- descricao
- categoria
- data_ocorrencia
- imagem
- status
- criado_em
Status:
- disponivel
- retirado
Relacionamentos confirmados:
- objetos.usuario_id → usuarios.id
- objetos.polo_id → polos.id
O campo imagem continua existindo na tabela objetos, mas o novo fluxo de fotos usa a tabela fotos_objetos.
Tabela fotos_objetos
Tabela criada com sucesso.

Campos:
- id
- objeto_id
- foto_numero
- caminho
- criado_em
Relacionamento:

fotos_objetos.objeto_id → objetos.id

Configurações:
- Cada objeto pode ter até 3 fotos.
- foto_numero aceita posições de 1 a 3.
- A combinação objeto_id + foto_numero é única.
- Ao excluir um objeto, suas fotos cadastradas no banco são excluídas em cascata.
Tabela solicitacoes
Ainda existe no banco, mas não será utilizada no novo fluxo. Não foi apagada.

6. Estrutura de arquivos
Achei/
├── index.php
├── login.php
├── cadastro.php
├── buscar.php
├── cadastrar_objeto.php
├── detalhes.php
├── minhas_solicitacoes.php
├── admin.php
├── config/
│   └── conexao.php
├── includes/
│   ├── cabecalho.php
│   ├── menu.php
│   ├── rodape.php
│   └── funcoes.php
├── actions/
│   ├── salvar_usuario.php
│   ├── salvar_objeto.php
│   └── solicitar_devolucao.php
└── assets/
    ├── css/
    │   └── style.css
    ├── js/
    │   └── script.js
    └── img/
        └── objetos/


Alguns arquivos da estrutura ainda estão vazios ou precisam ser adaptados ao funcionamento atual.

Os arquivos minhas_solicitacoes.php e actions/solicitar_devolucao.php são antigos e não devem ser utilizados no fluxo atual de devolução.

7. Implementações concluídas
- [x] Estrutura inicial de pastas criada.
- [x] Banco achei_db criado.
- [x] Conexão PDO testada.
- [x] Escopo do projeto redefinido com o grupo.
- [x] Perfis aluno, funcionario e admin definidos no banco.
- [x] Tabela polos criada.
- [x] Endereço de polos dividido em campos.
- [x] Usuários vinculados a polos.
- [x] Objetos vinculados a polos.
- [x] Status de objetos adaptado para disponivel e retirado.
- [x] Campo tipo removido da tabela objetos.
- [x] Campo local removido da tabela objetos.
- [x] Relacionamentos entre as tabelas conferidos.
- [x] objetos.polo_id definido como obrigatório.
- [x] Wireframe recebido e analisado.
- [x] Menu principal implementado.
- [x] Página inicial implementada.
- [x] Consulta de objetos disponíveis na página inicial.
- [x] Card de objeto exibindo título, descrição, categoria e polo.
- [x] Cadastro de usuário implementado.
- [x] Identificação de perfil pelo domínio de e-mail implementada no cadastro.
- [x] Tabela fotos_objetos criada.
- [x] Pasta assets/img/objetos/ criada.
- [x] Formulário de cadastro de objetos criado.
- [x] Campo de envio de até 3 fotos adicionado ao formulário.
- [x] Processamento do cadastro de objetos criado com validação de dados e imagens.
- [x] Tela de login criada.

8. Estado atual do código
config/conexao.php
Conexão PDO criada e testada com sucesso.
includes/funcoes.php
Contém:
- redirecionar()
- usuarioLogado()
- exigirLogin()
- exigirPerfil()
cadastro.php
Formulário de cadastro de usuário criado.
actions/salvar_usuario.php
Implementa:
- Validação de e-mail.
- Identificação do perfil pelo domínio.
- Validação de senha e confirmação.
- Verificação de e-mail duplicado.
- Salvamento de senha com password_hash().
O cadastro de funcionário ainda não inclui o fluxo de seleção/criação de polo.
includes/menu.php
Menu implementado com:
- Logo Achei!
- Campo de pesquisa
- Entrar e Cadastro quando não há login
- Links conforme o perfil quando há login
Alguns links apontam para páginas que ainda não foram criadas.
index.php
Exibe os objetos disponíveis mais recentes, com limite de 6 registros.

A página inicial foi testada com um objeto de exemplo e funcionou.
cadastrar_objeto.php
Formulário criado com:
- Nome do objeto
- Descrição
- Categoria
- Data em que foi encontrado
- Upload de até 3 fotos
A página exige perfil funcionario.
actions/salvar_objeto.php
Processamento criado com:
- Validação dos campos.
- Validação da data.
- Validação de categoria.
- Limite de 3 fotos.
- Tipos de imagem permitidos: JPG, PNG e WEBP.
- Limite de 5 MB por foto.
- Nome aleatório para os arquivos.
- Salvamento de fotos em assets/img/objetos/.
- Inserção das fotos em fotos_objetos.
- Transação do banco de dados.
- Remoção de arquivos enviados caso ocorra falha.
O formulário e o processamento ainda precisam ser testados juntos.
login.php
Tela de login criada com:
- Campo de e-mail
- Campo de senha
- Consulta do usuário no banco
- Verificação da senha com password_verify()
- Armazenamento de usuario_id, nome, email, perfil e polo_id na sessão
- Regeneração do ID da sessão após login
O login ainda não foi testado. A conta de funcionário inserida manualmente no banco pode não ter uma senha válida em formato password_hash().

9. Dados de teste
Foi inserido um polo de teste:
- Nome: Polo de Teste
- CEP: 12245000
- Cidade: São José dos Campos
- Estado: SP
Foi inserido um funcionário de teste, considerado com:
- id = 1
- polo_id = 1
- Perfil: funcionario
Foi inserido um objeto de teste:
- Título: Garrafa térmica azul
- Categoria: Acessórios
- Descrição: Garrafa térmica azul encontrada na biblioteca.
- Status: disponivel
O objeto foi exibido corretamente na página inicial.

10. Próximas etapas
1. Testar o login com uma conta que tenha senha válida.
2. Corrigir ou recriar a senha do funcionário de teste, se necessário.
3. Confirmar que o login preenche corretamente os dados da sessão.
4. Testar o acesso ao formulário de cadastro de objetos.
5. Testar o cadastro de objeto sem foto.
6. Testar o cadastro de objeto com 1, 2 e 3 fotos.
7. Testar a rejeição de mais de 3 fotos.
8. Implementar cadastro e vínculo de polos para funcionários.
9. Implementar consulta automática de CEP pelo ViaCEP.
10. Implementar busca e filtros para alunos.
11. Implementar detalhes dos objetos e contato da coordenação.
12. Implementar gerenciamento de objetos por funcionários, limitado ao polo vinculado.
13. Definir e implementar permissões do administrador.
14. Adaptar e revisar o visual conforme o wireframe.
15. Testar permissões, cadastro, pesquisa e funcionamento geral.

11. Instruções para continuar em outra conversa
Continuar a partir deste checkpoint, sem recriar o banco ou repetir as etapas concluídas.

Explicar em português simples, passo a passo, com comandos e caminhos exatos. Orientar uma etapa por vez e aguardar o resultado do usuário antes de avançar.

Não implementar cadastro de objetos por alunos nem solicitações de devolução.

Manter o funcionário vinculado a um único polo.

Não tratar @funcionario.unisa.br como domínio real; ele é fictício para a demonstração acadêmica.

Próximo passo previsto: testar o login e verificar se a conta de funcionário de teste possui uma senha válida.

## Telas de busca e detalhes — criadas
- `buscar.php`: consulta objetos disponíveis no banco; busca textual por `q`; filtros por polo e categoria; cartões e link para detalhes.
- `detalhes.php`: consulta objeto por `id`, mostra dados do polo, descrição/data e fotos de `fotos_objetos` com fallback para `objetos.imagem`.
- CSS novo foi adicionado em blocos próprios para busca e detalhes. Os seletores da navbar não foram alterados.
- Atenção: a tabela `objetos` não possui coluna específica de local encontrado; o código mostra `descricao` como informação do local/descrição conforme esquema atual.
- Testar no XAMPP: listagem sem filtros, busca, filtros, abrir detalhes com foto e sem foto, responsividade. A miniatura é interativa via clique.


## Atualização — tela principal (01/11/2026)
- O `index.php` foi ajustado para apresentar a listagem de itens no formato de cartões, com filtros por polo e categoria, conforme a referência visual enviada.
- Os cartões consultam os dados existentes em `objetos`, `polos` e `fotos_objetos`, e abrem `detalhes.php?id=...`.
- Implementada paginação de 8 itens por página e aplicação automática dos filtros ao selecionar polo ou tipo.
- O HTML e os estilos da navbar foram preservados; foram adicionados apenas estilos específicos da área de listagem e paginação.
- Pendente: testar no navegador com sessão autenticada, verificar filtros, paginação, objetos sem foto e conferir o visual em desktop e celular.

## Segurança por perfil (01/11/2026)
- As páginas de início, busca, detalhes e Minha conta exigem sessão com perfil `aluno`, `funcionario` ou `admin`.
- Cadastro de objetos e sua ação de gravação exigem perfil `funcionario`; `admin.php` e a ação de provisionamento exigem perfil `admin`.
- Funcionários aprovados podem listar, editar e excluir objetos do próprio polo em `gerenciar_objetos.php`; as ações usam token CSRF e validam o vínculo de polo no servidor. Objetos com registros antigos em `solicitacoes` não podem ser excluídos e podem ser marcados como retirados.
- Administradores também acessam `gerenciar_objetos.php` pelo menu e podem editar ou excluir objetos de qualquer polo. As rotas verificam o perfil no servidor; funcionários continuam limitados ao polo da sessão.
- O administrador pode editar nome, e-mail e polo vinculado dos usuários, excluir contas sem registros associados, e editar/excluir polos. Exclusões são bloqueadas quando há objetos ou usuários dependentes para preservar as relações e o histórico.
- O cadastro público cria alunos e aceita solicitações de funcionário pelo e-mail fictício `@funcionario.unisa.br`. O funcionário informa os dados do polo e o CEP preenche logradouro, bairro, cidade e UF via ViaCEP; a conta fica pendente até o administrador aprová-la com um botão.
- Senhas novas exigem pelo menos 8 caracteres, incluindo letra maiúscula, minúscula e número. Novas contas de administrador provisionadas pelo painel mantêm mínimo de 12 caracteres com esses mesmos requisitos.
- A aprovação administrativa só libera a conta já vinculada ao polo informado no cadastro. Funcionários pendentes não conseguem entrar nem abrir páginas internas. Não foi necessária nova migração: são usados `usuarios.polo_id`, `usuarios.provisionado_por` e os campos existentes de `polos`.
- A migração `database/migrations/20261101_perfis_provisionados.sql` foi aplicada ao banco local `achei_db`. A primeira conta administradora foi criada para `admin123@achados.com`; a senha aleatória foi entregue ao responsável nesta conversa. Há uma conta antiga de funcionário aguardando aprovação no painel administrativo.
- Acesso HTTP direto a `config/` e `includes/` é negado por `.htaccess`.
- O domínio `@funcionario.unisa.br` não é usado como prova de identidade nem concede perfil.

