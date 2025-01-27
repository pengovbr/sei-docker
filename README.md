# SEI-DOCKER

```
Atenção. Mudanças Importantes

01/2025

Agora, a partir da versão 3.6.0, a imagem base para o SqlServer será a 2019. Havia problemas com versões do kernel específicas.
Caso tenha um ambiente em uso nesse projeto com SqlSserver2017, não atualize a versão. Mantenha na 3.5.2 desse projeto.
Caso use um banco de dados externo ou use outro banco de dados não precisa se preocupar.
Verificar as alterações nas notas de release:
https://github.com/pengovbr/sei-docker/releases

=========

05/2024
Incluído suporte ao SEI5
Algumas mudanças estruturais nos arquivos envlocal.env para o ecossitema de dev e infra.
Verifique os detalhes na nota de release:
https://github.com/spbgovbr/sei-docker/releases/tag/3.5.0

Considere essas alterações ao ler os manuais e Readme(s), pois os mesmos ainda não foram atualizados.

=========

04/2024
Incluímos o traefik como balanceador do ecossistema. Ele está substituindo o haproxy-cloud que usávamos e parou de receber atualização, o que prejudicava o uso em versões docker mais recentes.
Portanto caso use alguma automação aponte para a tag 2.1.1, ela é a última com o haproxy como balanceador default.

A partir de agora (versão 3.0.0 do projeto) iremos adotar o traefik.

Esta v3 também conta com a possibilidade do SEI5, verificar notas de release.
Observção importante: para o SEI5 foram cridas novas imagens de app, agendador, banco Mysql e Solr. Portanto será necessário alterar o envlocal.env apontando para as respectivas imagens antes de subir o SEI5.

=========

Mudanças Importantes - 07/2023

Desde 07/2023 fizemos uma adaptação nesse projeto trazendo diversas melhorias que foram implementadas no projeto super-docker.
Desta forma o projeto sei-docker aqui listado precisou ser modificado em sua estrutura para atender aos novos requisitos.
É exatamente o mesmo projeto de antes, porém com novas pastas e funcionalidades, suportando por exemplo o sei4.1.

Para diminuir o impacto de possíveis integrações que usam esse repositório, criamos a branch sei4-docker-inicial. Nessa branch está o projeto sei-docker em sua antiga estrutura.
Portanto caso esteja usando alguma esteira ou automação que dependa desse repositório, e não queira fazer as adaptações necessárias para a nova estrutura, basta apontar para essa branch sei4-docker-inicial.

No entanto, recomendamos usar a branch main pois será ela que vai receber novas atualizações/correções.
```

## O que é

O SEI-Docker é o projeto disponibilizado para provisionamento de ambientes do SEI usando a tecnologia docker e os orquestradores docker-compose, cattle ou kubernetes.

## Para quem

O projeto atende a qualquer dos profissionais que desejem subir uma instância do SEI entre eles:
- desenvolvedores
- arquitetos
- analistas de testes
- analistas de segurança (para avaliação/mapeamento de eventuais vulnerabilidades)
- profissionais de TI envolvidos nas atividades de dev e sustentação do SEI

## Para que

- desenvolvimento/debug do código-fonte do SEI
- desenvolvimento/debug do código-fonte dos módulos do SEI
- disponibilização de ambientes diversos para o SEI:
	- teste
	- treinamento
	- avaliação
- ambientar profissional de infra com os serviços/componentes necessários para a implantação e sustentação do SEI

# Organização

Podemos dividir o projeto em 3 grandes áreas:

- ### Dev

	Na pasta dev há um Automatizador (Makefile) pronto para subir uma instância do SEI escolhendo a base de dados e com o xdebug habilitado. Apropriada para subir um ambiente local montando o código fonte do SEI. Desta forma você pode usar o seu editor / debugger preferido na edição do código.

	Nessa modalidade o projeto disponibiliza para o desenvolvedor os seguintes componentes:
	- app  (serviço apache para o SEI)
	- database (mariadb, sqlserver ou oracle)
	- memcached
	- jod
	- solr
	- mailcatcher (servidor smtp e mailcatcher para visualizar os emails enviados)

	Para maiores informações, acesse a pasta dev e leia o Readme respectivo ou [clique aqui](dev/README.md) para abrir diretamente

- ### Arquitetos e profissionais de infra

	Na pasta infra há um Automatizador (Makefile) pronto para que um profissional de infra suba rapidamente a estrutura completa do SEI usando o docker-compose, com opçoes de: 
	- openldap
	- simulador de servidor de email
	- solr admin
	- memcached admim
	- instalacao automática de módulos
	- orgao, siglas e descricoes do ambiente
	- http ou https, com cert proprio ou auto-assinado
	- entre outras customizações

	Usado para criar ambientes de teste, validação, treinamento, tanto para a área técnica quanto para a área negocial

	Há a possibilidade de subir toda a infra em uma única vm ou gerar as receitas kubernetes ou Cattle para rodar em seu cluster local

	Para maiores informações, acesse a pasta infra e leia o Readme respectivo ou [clique aqui](infra/README.md) para abrir diretamente

- ### Containers

	Na pasta containers encontram-se as receitas para as imagens docker. Os conteineres já existem de forma pública para você rodar o projeto em sua máquina local ou infra. Não é necessário entrar aqui ou conhecer essa área para rodar o SEI.

	Mas caso mesmo assim deseje buildar as imagens por conta própria, modificá-las ou usar o seu próprio registry; basta acessar essa pasta. Nela estão as receitas docker usadas, bem como as automatizações (Makefile) para criar seus próprios conteineres em seu próprio Docker Registry.

	Para maiores informações, acesse a pasta containers e leia o Readme respectivo ou [clique aqui](containers/README.md) para abrir diretamente

## Testes

Caso faça alguma alteração no projeto, rode os testes propostos para garantir que pelo menos o básico está funcionando de acordo com o esperado.

Dentro de cada grande área há uma pasta de testes.
Nessas pastas ficam os testes automatizados para cada área:

- **containers/tests**: existem diversos testes para os conteineres.

	Rode ``` make test-containers ``` para executar uma bateria com todos os subtestes envolvidos. Aqui ele vai criar os conteineres com a tag test e tentará fazer o push bem como outras operações previstas no makefile

- **dev/tests**: aqui ele irá usar os modelos de envfiles fornecidos, subirá o SEI para cada um deles e rodará testes de criacao de processo/documento para saber se está ou não funcionando.

	Rode ``` make tests-all-bases ``` para executar a bateria com todos os subtestes envolvidos.

- **infra/tests**: existem diversos testes para a área de infra. Como são muitas possibilidades de customização esse teste é demorado. O automatizador vai subir e destruir o SEI diversas vezes variando as formas e possibilidades de customização.

	Rode ``` make test_lineup_completa ``` para executar todos os subtestes envolvidos. Dependendo da necessidade pode executar os subtestes isoladamente, basta digitar ``` make help ``` para uma lista completa das opções disponíveis.



# Pré-Requisitos

Para utilizar esse projeto você precisa de:
- código fonte do SEI
- docker
- docker-compose



# Dúvidas Sugestões Bugs ou Contribuição

Dúvidas, sugestões ou reporte de bugs usar a parte de issues: https://github.com/spbgovbr/sei-docker/issues

Para contribuir basta fazer o pull request. Aconselhável antes alinhar os requisitos com algum project owner.

