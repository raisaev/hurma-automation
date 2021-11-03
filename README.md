# isaev :hurma: hurma-automation

## 0. installation

#### 0.1. установка docker

```shell
sudo apt install docker.io
sudo gpasswd -a $USER docker
```

#### 0.2. установка docker-compose

```shell
sudo wget –O /usr/local/bin/docker-compose https://github.com/docker/compose/releases/download/v2.6.0/docker-compose-linux-x86_64
sudo chmod +x /usr/local/bin/docker-compose

docker-compose --version
```

## 1. run

```shell
# hurma-automation@attendance-2-1602488346038.iam.gserviceaccount.com must have write access
export GOOGLE_APPLICATION_CREDENTIALS=""

cd dev && docker-compose up -d --build
docker-compose exec php /bin/bash
```

## 2. test

```shell
# https://docs.google.com/spreadsheets/d/14PJJQ3il5yvmk-Gx7nDDr5T1ksELrbxdq-eW3j-aitw
sheet="14PJJQ3il5yvmk-Gx7nDDr5T1ksELrbxdq-eW3j-aitw"

php bin/console google:parse-sheet ${sheet} A1:D200 one
php bin/console hurma:process-coins ${sheet} A1:D200 one
```
