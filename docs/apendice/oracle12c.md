
# Oracle 12c

A versão do Oracle disponibilizada aqui é a 11G. Já há conteiner pré-compilado pronto para uso gratuito.

Disponibilizamos aqui também uma receita docker caso deseje construir, você mesmo, uma imagem para o Oracle 12c para testes.
De forma alternativa vc também pode usar sua própria receita ou subir com um conteiner já existente de terceiros. Bastando alterar os env files dos ecossistemas dev ou infra.

Não podemos disponibilizar a imagem pronta, como fizemos com o Oracle 11g pois a versão 12c exige registro na Oracle para uso.
O uso pessoal/testes é gratuito, porém a Oracle exige o registro em seu site antes de baixar.

Caso deseje compilar uma imagem para o Oracle 12c usando nossa receita:

1. Faça o registro no site da Oracle: container-registry.oracle.com

2. ``` docker login container-registry.oracle.com ```
  e siga com o login no seu browser

3. ```docker pull container-registry.oracle.com/database/enterprise:12.2.0.1-slim```

4. ```cd containers```
  ``` make build-conteiner-oracle12c-sei50 ```
  ou
  ```make build-conteiner-oracle12c-sei51 ```

7. Feito isso será construído um container para o Oracle 12c

## Para subir no ecossistema infra

Caso deseje usar a imagem no ecossistema infra basta adicionar os parametros referentes a imagem Oracle12c, rodando por exemplo:

    cat envlocal-example-oracle12c-sei5.env >> envlocal.env

depois

    make setup


## Para subir no ecossistema dev

Caso deseje usar a imagem no ecossistema dev basta referenciar o oracle12c

    make base=oracle12c-sei5 config
    make up

**Importante:**

Acessar o diretorio onde os fontes estão montados e adicionar o nome do Plugabble Database (orclpdb1.localdomain) no array de banco de dados

Localize:

    'Servidor' => getenv('DATABASE_HOST') ,

Substitua por:

    'Servidor' => getenv('DATABASE_HOST') . "/orclpdb1.localdomain",

Faça isso para o sip e para o sei

O container de aplicação fica tentando reconectar indefinidamente, portanto após o banco subir e a alteração na string de conexão feita, deve subir sem problemas.

Isso é necessário pois a imagem montada exige que se informe qual serviço será feita a conexão. Um pull request será bem vindo para eliminar essa necessidade. No caso tem que criar uma entrada de serviço default para esse PDB.
