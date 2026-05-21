#!/bin/bash
# NOTE:
# We need this because the variable contains / in it.
# More info here https://unix.stackexchange.com/questions/255789/is-there-a-way-to-prevent-sed-from-interpreting-the-replacement-string/255869#255869
ESCAPED_CI_PROJECT_DIR=$(sed -e 's/[&\\/]/\\&/g; s/$/\\/' -e '$s/\\$//' <<<"$CI_PROJECT_DIR")
sed -i "s/CI_PROJECT_DIR/$ESCAPED_CI_PROJECT_DIR/g" zap/zap_config.conf

ESCAPED_TARGET=$(sed -e 's/[&\\/]/\\&/g; s/$/\\/' -e '$s/\\$//' <<<"$TARGET")
sed -i "s/TARGET/$ESCAPED_TARGET/g" zap/context.context

/zap/zap-full-scan.py -d \
                      -t https://brokencrystals.com \
                      -r $CI_PROJECT_DIR/$ZAP_REPORT.html \
                      -w $CI_PROJECT_DIR/$ZAP_ALERT_REPORT.md \
                      -n $CI_PROJECT_DIR/zap/context.context \
                      -U $USERNAME

returnCode=0
if grep -q "Instances" $CI_PROJECT_DIR/$ZAP_ALERT_REPORT.md; then
  head -n 20 $CI_PROJECT_DIR/$ZAP_ALERT_REPORT.md
  echo "DAST RESULT: There are some vulnerabilities that ZAP has found (those visible here may not be the only ones). See the detailed report for more  information."
  returnCode=1
fi

exit $returnCode