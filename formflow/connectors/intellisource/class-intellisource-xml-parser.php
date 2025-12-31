<?php
/**
 * IntelliSource XML Parser
 *
 * Parses XML responses from the PowerPortal IntelliSOURCE API.
 *
 * @package FormFlow
 * @subpackage Connectors
 * @since 2.0.0
 */

namespace ISF\Connectors\IntelliSource;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class IntelliSourceXmlParser
 */
class IntelliSourceXmlParser {

    /**
     * Parse XML string to array
     *
     * @param string $xml The XML string to parse
     * @return array The parsed data
     * @throws \Exception If XML is invalid
     */
    public static function parse(string $xml): array {
        if (empty($xml)) {
            return [];
        }

        // Suppress errors and use custom error handling
        $use_errors = libxml_use_internal_errors(true);

        // SECURITY: Disable external entity loading to prevent XXE attacks
        $previous_entity_loader = null;
        if (\PHP_VERSION_ID < 80000) {
            // libxml_disable_entity_loader is deprecated in PHP 8.0+
            // In PHP 8.0+, external entity loading is disabled by default
            $previous_entity_loader = libxml_disable_entity_loader(true);
        }

        try {
            // SECURITY: Use LIBXML_NONET to prevent network access and LIBXML_NOENT to not substitute entities
            $doc = new \SimpleXMLElement($xml, LIBXML_NONET | LIBXML_NOCDATA);
            $result = self::xml_to_array($doc);

            // Clear any errors
            libxml_clear_errors();
            libxml_use_internal_errors($use_errors);

            // Restore entity loader state for PHP < 8.0
            if ($previous_entity_loader !== null) {
                libxml_disable_entity_loader($previous_entity_loader);
            }

            return $result;
        } catch (\Exception $e) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            libxml_use_internal_errors($use_errors);

            // Restore entity loader state for PHP < 8.0
            if ($previous_entity_loader !== null) {
                libxml_disable_entity_loader($previous_entity_loader);
            }

            $error_message = 'XML Parse Error';
            if (!empty($errors)) {
                $error_message .= ': ' . $errors[0]->message;
            }

            throw new \Exception($error_message);
        }
    }

    /**
     * Convert SimpleXML element to array
     *
     * @param \SimpleXMLElement $xml
     * @return array
     */
    private static function xml_to_array(\SimpleXMLElement $xml): array {
        $result = [];

        // Get attributes
        foreach ($xml->attributes() as $key => $value) {
            $result['@' . $key] = (string) $value;
        }

        // Get child elements
        foreach ($xml->children() as $key => $value) {
            $child_value = self::parse_node($value);

            // Handle multiple elements with same name
            if (isset($result[$key])) {
                if (!is_array($result[$key]) || !isset($result[$key][0])) {
                    $result[$key] = [$result[$key]];
                }
                $result[$key][] = $child_value;
            } else {
                $result[$key] = $child_value;
            }
        }

        // If no children, return string value
        if (empty($result)) {
            return (string) $xml;
        }

        return $result;
    }

    /**
     * Parse a single XML node
     *
     * @param \SimpleXMLElement $node
     * @return mixed
     */
    private static function parse_node(\SimpleXMLElement $node): mixed {
        // Check if node has children
        if ($node->count() > 0) {
            return self::xml_to_array($node);
        }

        // Check if node has attributes
        $attrs = [];
        foreach ($node->attributes() as $key => $value) {
            $attrs['@' . $key] = (string) $value;
        }

        if (!empty($attrs)) {
            $attrs['_value'] = (string) $node;
            return $attrs;
        }

        // Return string value
        return (string) $node;
    }

    /**
     * Parse validation response
     *
     * @param string $xml
     * @return array
     */
    public static function parse_validation(string $xml): array {
        $data = self::parse($xml);

        // Normalize response structure
        $result = [
            'valid' => false,
            'error_cd' => '',
            'error_message' => '',
            'customer' => [],
        ];

        // Check for validation node
        if (isset($data['validation'])) {
            $validation = $data['validation'];
            $result['valid'] = ($validation['valid'] ?? 'N') === 'Y';
            $result['error_cd'] = $validation['error_cd'] ?? $validation['errorCode'] ?? '';
            $result['error_message'] = $validation['error_message'] ?? '';

            // Extract customer data
            if (isset($validation['customer'])) {
                $result['customer'] = $validation['customer'];
            }
        }

        // Check for direct error nodes
        if (isset($data['error_cd'])) {
            $result['error_cd'] = $data['error_cd'];
        }
        if (isset($data['error_message'])) {
            $result['error_message'] = $data['error_message'];
        }

        // Merge all data for access
        $result = array_merge($data, $result);

        return $result;
    }

    /**
     * Parse enrollment response
     *
     * @param string $xml
     * @return array
     */
    public static function parse_enrollment(string $xml): array {
        $data = self::parse($xml);

        $result = [
            'success' => false,
            'confirmation_no' => '',
            'caNo' => '',
            'error_cd' => '',
            'error_message' => '',
        ];

        // Check for enrollment node
        if (isset($data['enrollment'])) {
            $enrollment = $data['enrollment'];
            $result['success'] = ($enrollment['status'] ?? '') === 'success'
                || !empty($enrollment['confirmation_no'])
                || !empty($enrollment['caNo']);
            $result['confirmation_no'] = $enrollment['confirmation_no'] ?? '';
            $result['caNo'] = $enrollment['caNo'] ?? '';
            $result['error_cd'] = $enrollment['error_cd'] ?? '';
            $result['error_message'] = $enrollment['error_message'] ?? '';
        }

        // Check direct nodes
        if (isset($data['confirmation_no'])) {
            $result['confirmation_no'] = $data['confirmation_no'];
            $result['success'] = true;
        }
        if (isset($data['caNo'])) {
            $result['caNo'] = $data['caNo'];
            $result['success'] = true;
        }

        $result = array_merge($data, $result);

        return $result;
    }

    /**
     * Parse scheduling response
     *
     * @param string $xml
     * @return array
     */
    public static function parse_scheduling(string $xml): array {
        $data = self::parse($xml);

        $result = [
            'fsr' => '',
            'caNo' => '',
            'slots' => [],
        ];

        // Check for scheduling node
        if (isset($data['scheduling'])) {
            $scheduling = $data['scheduling'];
            $result['fsr'] = $scheduling['fsr'] ?? $scheduling['FSR'] ?? '';
            $result['caNo'] = $scheduling['caNo'] ?? $scheduling['CA_NO'] ?? '';

            // Parse slots
            if (isset($scheduling['slots']['slot'])) {
                $slots = $scheduling['slots']['slot'];
                // Normalize to array
                if (!isset($slots[0])) {
                    $slots = [$slots];
                }
                foreach ($slots as $slot) {
                    $result['slots'][] = [
                        'date' => $slot['date'] ?? '',
                        'time' => $slot['time'] ?? '',
                        'available' => ($slot['available'] ?? 'Y') === 'Y',
                    ];
                }
            }
        }

        // Check direct nodes
        if (isset($data['fsr'])) {
            $result['fsr'] = $data['fsr'];
        }
        if (isset($data['caNo'])) {
            $result['caNo'] = $data['caNo'];
        }

        $result = array_merge($data, $result);

        return $result;
    }

    /**
     * Build XML request from array
     *
     * @param array $data
     * @param string $root_element
     * @return string
     */
    public static function build(array $data, string $root_element = 'request'): string {
        $xml = new \SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><{$root_element}/>");

        self::array_to_xml($data, $xml);

        return $xml->asXML();
    }

    /**
     * Convert array to XML
     *
     * @param array $data
     * @param \SimpleXMLElement $xml
     */
    private static function array_to_xml(array $data, \SimpleXMLElement $xml): void {
        foreach ($data as $key => $value) {
            // Handle attributes
            if (strpos($key, '@') === 0) {
                $xml->addAttribute(substr($key, 1), $value);
                continue;
            }

            // Handle nested arrays
            if (is_array($value)) {
                // Check if numeric keys (list of items)
                if (isset($value[0])) {
                    foreach ($value as $item) {
                        $child = $xml->addChild($key);
                        if (is_array($item)) {
                            self::array_to_xml($item, $child);
                        } else {
                            $child[0] = $item;
                        }
                    }
                } else {
                    $child = $xml->addChild($key);
                    self::array_to_xml($value, $child);
                }
            } else {
                $xml->addChild($key, htmlspecialchars((string) $value));
            }
        }
    }
}
