#!/bin/bash
# FormFlow Pro - Accessibility Check
# Usage: ./scripts/a11y-check.sh

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}  FormFlow Pro - Accessibility Check${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""

cd "$(dirname "$0")/.."

echo -e "${YELLOW}Scanning PHP templates for accessibility issues...${NC}"
echo ""

node scripts/accessibility-scan.cjs

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}✓ No critical accessibility issues found!${NC}"
else
    echo ""
    echo -e "${RED}✗ Accessibility issues detected. See above for details.${NC}"
    exit 1
fi
