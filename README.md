# hurma-automation

[![build-status](https://github.com/raisaev/hurma-automation/actions/workflows/docker-publish.yml/badge.svg)](https://github.com/raisaev/hurma-automation/actions/workflows/docker-publish.yml)

---

> google service account JSON key must be saved to ./docker/google_auth.json

> google service account must have write access to google sheet

> google service account must have workload identity configured
> https://cloud.google.com/kubernetes-engine/docs/how-to/workload-identity#gcloud_4

```shell
SERVICE_ACCOUNT_NAME=
GOOGLE_PROJECT=

gcloud iam service-accounts add-iam-policy-binding ${SERVICE_ACCOUNT_NAME} \
    --role roles/iam.workloadIdentityUser \
    --member "serviceAccount:${GOOGLE_PROJECT}.svc.id.goog[hurma/hurma-automation]"
```

---

make command can be used for docker-compose up|down and so on operations
```shell
make up
make down
```

```shell
sheet="14PJJQ3il5yvmk-Gx7nDDr5T1ksELrbxdq-eW3j-aitw"

php bin/console google:parse-sheet ${sheet} A1:D200 one
php bin/console hurma:process-coins ${sheet} A1:D200 one
```
