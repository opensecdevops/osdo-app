#!/bin/bash

TODAY=$(date +%Y-%m-%d)

echo ${DD_SCAN_FILE}

curl --location --request POST "${DD_SERVER}/api/v2/import-scan/" \
    -H 'accept: application/json' \
    -H "Authorization: Token ${DD_API_KEY}" \
    -F "scan_date=${TODAY}" \
    -F "minimum_severity=${DD_SCAN_MINIMUM_SEVERITY}" \
    -F "active=${DD_SCAN_ACTIVE}" \
    -F "verified=${DD_SCAN_VERIFIED}" \
    -F "scan_type=${DD_SCAN_TYPE}" \
    -F "engagement=${DD_ENGAGEMENTID}" \
    -F "file=@${DD_SCAN_FILE};type=application/json" \
    -F "close_old_findings=${DD_SCAN_CLOSE_OLD_FINDINGS}" \
    -F "environment=${DD_SCAN_ENVIRONMENT}" \
    -F "product_name=${DD_PRODUCT_NAME}"  \
    -F 'create_finding_groups_for_all_findings=true' \
    -F 'group_by=component_name+component_version'
