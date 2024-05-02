## Problem Track

"Problem Track" is the ultimate solution for organizations seeking to enhance their problem resolution processes, drive operational efficiency, and deliver exceptional customer experiences.

### Dependências

- Docker
- Docker Compose

### To run

#### Clone Repository

```
$ git clone git@github.com:SI-DABE/problem-track.git
$ cd problem-track
```

#### Define the env variables

```
$ cp .env.example .env
```

#### Define the file database

```
$ touch ./database/problems.txt
$ chmod 665 ./database/problems.txt
```

#### Install the dependencies

```
$ ./run composer install
```

#### Up the containers

```
$ docker compose up -d
```

ou

```
$ ./run up -d
```

#### Run the tests

```
$ docker compose run --rm php ./vendor/bin/phpunit tests --color
```

ou

```
$ ./run test
```

#### Run the linters

[PHPCS](https://github.com/PHPCSStandards/PHP_CodeSniffer/)

```
$ ./run phpcs
```

[PHPStan](https://phpstan.org/)

```
$ ./run phpstan
```

Access [localhost](http://localhost)

### Teste de API

```shell
curl -H "Accept: application/json" localhost/problems
```
