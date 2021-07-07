# :house: API Challenge - UTransfer

Este é um projeto que simula um sistema de transferências simples. Nele é possível criar um usuário, além da realização de login e transferências.

## :gear: Requisitos & Instalação

- Ter o docker instalado
- Rodar o comando  
  `docker-compose up -d`
- Acesar o bash do docker e rodar os comandos  
  `php artisan migrate`  
  `php artisan passport:install`  
  `php artisan db:seed`


## :man_technologist: Utilização

Apos a criação do usuario é possivel obter um token para acesso ou após realizar login.

### Usuário
`POST /api/register`
```
{
    "name": "Foo Bar",
    "email": "foo@bar.com",
    "password": "foobar",
    "cnpj" ou "cpf": "47285749000178" // Apenas digitos
    "user_type_id": 1 //Baseado no id criado apos o seed
}
```

`POST /api/login`
```
{
    "email": "foo@bar.com",
    "password": "foobar"
}
```

### Transferências
`POST /api/transfer`
```
{
    "user_id_to": 21, // Usuario de destino na transferência
    "amount": 10.00 // Valor a ser transferido
}
```

## :hammer_and_wrench: Ferramentas utilizadas
- Laravel 7.3
- PHP 7.3
- Docker
