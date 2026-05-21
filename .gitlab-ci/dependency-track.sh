#!/bin/bash

curl --location --request "POST" "${DT_SERVER}/api/v1/bom" \
        -H "X-Api-Key: $DT_API_KEY" \
        -F "project=$DT_PROJECT_ID" \
        -F "projectVersion=$CI_COMMIT_BRANCH" \
        -F "bom=@sbom.json" -f