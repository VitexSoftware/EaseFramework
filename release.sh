#!/bin/bash
docker build -t vitexus/ease-framework .
docker push vitexus/ease-framework
cd debian
./deb-package.sh
