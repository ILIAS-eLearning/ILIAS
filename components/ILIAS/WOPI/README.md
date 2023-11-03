Docker und OnlyOffice
=====================

https://jira.sr.solutions/browse/SRAGRB-1718

```bash
docker-compose -f collabora_code.yml up
```

Lokale OnlyOffice Installation mit Docker

```
sudo docker run -i -t -d -p 8888:80 --restart=always onlyoffice/documentserver
open http://localhost:8888
# got through the setup
```

https://www.youtube.com/watch?v=fubXfITVvqE

Library für Testing

```
composer require champs-libres/wopi-lib:dev-master --ignore-platform-reqs
```
