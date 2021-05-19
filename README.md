# Transaction API Project

## 1. Introdução

A Transaction API é um entrypoint relacionado a transferência de valores. A Transaction API utiliza todos os princípios da arquitetura REST.

## 2. Entrypoint

### Entrypoint de Transação

### Descrição

O entrypoint é utilizado para realizar a transferência de valores entre clientes PicPay. Não é possível que um cliente do tipo 'lojista' faça uma transferência.

#### Entrypoint

`POST /transaction`

#### Requisição

##### Corpo da Requisição

```json
{
    "value" : 100.00,
    "payer" : 4,
    "payee" : 15
}
```

* `value` *(float)*
  * o valor da transação
* `payer` *(int)*
  * o id do usuário que realizará a transferência
* `payee` *(int)*
  * o id do usuário que receberá a transferência

#### Respostas

##### Códigos de Resposta

###### `201` - created

A requisição retornará o código de resposta `201` quando uma transação for realizada com sucesso. Também será retornado um payload contendo o status da transação e um código de transação.

```json
{
    "success": true,
    "transaction_id": "f38e6f9b-4773-416e-98a8-8afd1cd8630b",
}
```

###### `400` - bad request

A requisição retornará o código de resposta `400` quando ocorrer algum erro durante a transação. Esse erro estará relacionado a erros de domínio ou negócio como, por exemplo, o usuário não ter saldo suficiente ou haver uma negativa do autorizador externo. Também será retornado um payload contendo o status da transação, um código de erro e uma mensagem descritiva sobre o erro ocorrido. *Para mais informações consultar a tabela de erros abaixo.*

```json
{
    "success": false,
    "type": "saldo_insuficiente",
    "message": "O usuário com id 2 não possui saldo suficiente para transferir o valor de 100,00 reais."
}
```

###### `422` - unprocessable entity

A requisição retornará o código de resposta `422` quando ocorrer algum erro de validação de dados como, por exemplo, passar um tipo não aceito pela API ou faltar algum campo obrigatório na requisição. Também será retornado um payload contendo o status da transação, um código de erro, uma mensagem descritiva e um array de erros indicando quais campos não passou na validação.

```json
{
    "success": false,
    "type": "erro_de_validacao",
    "message": "Houve um erro de validação.",
    "errors": [
        "The value must be a number.",
        "The payee field is required."
    ]
}
```

###### `500` - internal error

A requisição retornará o código de resposta `500` quando ocorrer algum erro interno do sistema como, por exemplo, falha na comunicação com banco de dados. Também será retornado um payload contendo o status da transação, um código de erro e uma mensagem descritiva.

```json
{
    "success": false,
    "type": "erro_interno",
    "message": "Houve um erro interno no sistema."
}
```

##### Tipos de Resposta

Os tipos de respostas disponíveis são:

| Tipo de Erro | Descrição do Erro |
|:------------:|-------------------|
|`erro_de_validacao`|É retornado quando o payload tem algum erro de formatação ou tipos errados, por exemplo, mandar o valor como uma string.|
|`saldo_insuficiente`|É retornado quando o pagador não tem saldo suficiente na conta.|
|`transacao_nao_autorizada`|É retornado quando a transação não foi autorizada pelo agente autorizador externo.|
|`usuario_nao_autorizado`|É retornado quando o usuário não está autorizado a fazer uma transação. Por exemplo, um lojista não pode fazer transferências.|
|`transacao_para_o_mesmo_usuario`|É retornado quando o usuário tenta transferir dinheiro para ele mesmo.|
|`usuario_nao_encontrado`|É retornado quando algum dos usuários da transação não existe.|
|`erro_interno`|É retornado quando ocorre algum erro interno no sistema impossibilitando a finalização da transação.|
