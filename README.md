# Transferência API

Este projeto é uma API para realizar transferências entre usuários, utilizando Laravel Lumen como framework. A API é projetada para gerenciar transferências de maneira segura e eficiente, com recursos de autenticação e autorização.

## Funcionalidades

- **Transferência de Valores**: Permite que um usuário (pagador) transfira valores para outro usuário (beneficiário).
- **Validação de Usuários**: Verifica se os usuários existem e se podem realizar a transferência.
- **Autorização Externa**: Integra-se a um serviço externo para verificar a autorização da transferência.
- **Registro de Transações**: Registra todas as transferências realizadas, incluindo detalhes do pagador, beneficiário e valor.
- **Notificações**: Envia notificações ao beneficiário após a transferência ser concluída.

## Tecnologias Utilizadas

- **Laravel Lumen**: Framework PHP minimalista para APIs.
- **Eloquent ORM**: Para interação com o banco de dados.
- **Cache**: Utilizado para armazenar informações de usuários temporariamente e reduzir a carga no banco de dados.
- **Log**: Registro de eventos e erros para monitoramento e depuração.
