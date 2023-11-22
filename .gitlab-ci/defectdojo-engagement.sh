#!/bin/bash

TODAY=$(date +%Y-%m-%d)
ENDDAY=$(date -d "+${DD_ENGAGEMENT_PERIOD} days" +%Y-%m-%d)

ENGAGEMENTID=$(curl --fail --location --request POST "${DD_SERVER}/api/v2/engagements/" \
  -H "Authorization: Token ${DD_API_KEY}" \
  -H 'Content-Type: application/json' \
  --data-raw "{
    \"tags\": [\"GITLAB-CI\"],
    \"name\": \"#${CI_PIPELINE_ID}\",
    \"description\": \"${CI_COMMIT_DESCRIPTION}\",
    \"version\": \"${CI_COMMIT_REF_NAME}\",
    \"first_contacted\": \"${TODAY}\",
    \"target_start\": \"${TODAY}\",
    \"target_end\": \"${ENDDAY}\",
    \"reason\": \"string\",
    \"tracker\": \"${CI_PROJECT_URL}/-/issues\",
    \"threat_model\": \"${DD_ENGAGEMENT_THREAT_MODEL}\",
    \"api_test\": \"${DD_ENGAGEMENT_API_TEST}\",
    \"pen_test\": \"${DD_ENGAGEMENT_PEN_TEST}\",
    \"check_list\": \"${DD_ENGAGEMENT_CHECK_LIST}\",
    \"status\": \"${DD_ENGAGEMENT_STATUS}\",
    \"engagement_type\": \"CI/CD\",
    \"build_id\": \"${CI_PIPELINE_ID}\",
    \"commit_hash\": \"${CI_COMMIT_SHORT_SHA}\",
    \"branch_tag\": \"${CI_COMMIT_REF_NAME}\",
    \"deduplication_on_engagement\": \"${DD_ENGAGEMENT_DEDUPLICATION_ON_ENGAGEMENT}\",
    \"product\": \"${DD_PRODUCTID}\",
    \"source_code_management_uri\": \"${CI_PROJECT_URL}\",
    \"build_server\": ${DD_ENGAGEMENT_BUILD_SERVER},
    \"source_code_management_server\": ${DD_ENGAGEMENT_SOURCE_CODE_MANAGEMENT_SERVER},
    \"orchestration_engine\": ${DD_ENGAGEMENT_ORCHESTRATION_ENGINE}
  }" | jq -r '.id')

echo "DD_ENGAGEMENTID=${ENGAGEMENTID}" >> defectdojo.env
