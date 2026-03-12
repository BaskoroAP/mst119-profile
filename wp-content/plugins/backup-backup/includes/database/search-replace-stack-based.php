<?php

namespace BMI\Plugin\Database;


class BMIStackSearchReplace
{
    /**
     * Main entry point.
     * Detects the format (JSON, Serialized, or String) and routes to the correct parser.
     */
    public function replace($search, $replace, $data)
    {
        // 1. Handle Arrays/Objects (Recursion Base)
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->replace($search, $replace, $value);
            }
            return $data;
        }

        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $this->replace($search, $replace, $value);
            }
            return $data;
        }

        // 2. Handle Strings (The Parsing Logic)
        if (is_string($data)) {
            // A. Check for JSON first
            // We use native json_decode because writing a robust JSON parser in pure PHP is inefficient.
            // This handles the "Escaped Slashes" issue automatically.
            if ($this->is_json($data)) {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Recurse into the JSON structure
                    $replaced_data = $this->replace($search, $replace, $decoded);
                    // Re-encode (handles escaping automatically)
                    return json_encode($replaced_data);
                }
            }

            // B. Check for Serialization
            // If it looks serialized, we use our Custom Stack-Based Parser
            if (is_serialized($data)) {
                return $this->replace_serialized_stream($search, $replace, $data);
            }

            // C. Raw String Replacement
            // This is the leaf node of the recursion
            if (is_array($search) && is_array($replace) && count($search) === count($replace)) {
                return strtr($data, array_combine($search, $replace));
            } else {
                return str_replace($search, $replace, $data);
            }
        }

        // 3. Passthrough for other types (int, bool, null)
        return $data;
    }

    /**
     * A Stack-Based Parser for Serialized Strings.
     * instead of unserializing (which creates objects), we scan the string tokens.
     * When we find a string token 's:N:"..."', we isolate the content, recurse, and recalculate N.
     */
    private function replace_serialized_stream($search, $replace, $data)
    {
        $result = '';
        $pos = 0;
        $length = strlen($data);

        while ($pos < $length) {
            // Find the next potential string token 's:'
            $s_token_pos = strpos($data, 's:', $pos);

            // If no more tokens, append the rest and finish
            if ($s_token_pos === false) {
                $result .= substr($data, $pos);
                break;
            }

            // Append everything before the token (e.g., array keys, boolean markers 'b:1;')
            $result .= substr($data, $pos, $s_token_pos - $pos);

            // Validate if this is truly a length marker (s:123:)
            // We look for the colon after the number
            $second_colon_pos = strpos($data, ':', $s_token_pos + 2);

            if ($second_colon_pos === false) {
                // Malformed or just text that looks like 's:', skip it
                $result .= 's:';
                $pos = $s_token_pos + 2;
                continue;
            }

            // Extract length integer
            $len_str = substr($data, $s_token_pos + 2, $second_colon_pos - ($s_token_pos + 2));
            if (!is_numeric($len_str)) {
                // Not a valid serialization token
                $result .= substr($data, $s_token_pos, $second_colon_pos - $s_token_pos + 1);
                $pos = $second_colon_pos + 1;
                continue;
            }

            $old_len = (int) $len_str;

            // The content starts after 's:N:"' (quote is at second_colon_pos + 1)
            $content_start = $second_colon_pos + 2;

            // Check boundary safety
            if ($content_start + $old_len > $length) {
                // Malformed data, stop parsing
                $result .= substr($data, $s_token_pos);
                break;
            }

            // Extract the Inner Content
            $content = substr($data, $content_start, $old_len);

            // Process the inner content via the main replace routine.
            // This may recurse further if the content is JSON or nested serialization,
            // or it may simply perform a plain-text search/replace if it is just a string.
            $new_content = $this->replace($search, $replace, $content);

            // Reconstruct the token
            // If content changed, the length changes automatically here
            $new_len = strlen($new_content);
            $result .= "s:$new_len:\"$new_content\";";

            // Advance cursor: old position + header + quote + old_content + quote + semicolon
            // Header size = ($second_colon_pos - $s_token_pos + 1)
            // Total skip = (header) + 1 (quote) + old_len + 2 (quote + semicolon)
            $pos = $content_start + $old_len + 2;
        }

        return $result;
    }

    /**
     * Lightweight check to see if string MIGHT be JSON.
     */
    private function is_json($string)
    {
        if (!is_string($string) || $string === '')
            return false;
        $first = $string[0];
        return ($first === '{' || $first === '[');
    }
}