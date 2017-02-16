#!/bin/bash

BASE_DIR="bilingualwordpress"
CURRENT_DIR=`dirname ${0}`

cd ${CURRENT_DIR}/../..
zip -9 -r ${BASE_DIR}/bilingualwordpress.zip ${BASE_DIR}/LICENSE ${BASE_DIR}/README.md ${BASE_DIR}/bilingualwordpress.php ${BASE_DIR}/include
