<?php

function include_recursively(string $data, int $level = 0, $base = '.'): string
{
    if ($level > 0) {
        // now count opening and closing php tags
        $opening = substr_count($data, '<?');
        $closing = substr_count($data, '?>');

        // if there's parity, we need to add <?php at the end
        if ($opening === $closing) {
            $data .= '<?php';
        }
        if (str_starts_with($data, '<?php')) {
            // we need to remove <?php from the beginning of the file, if it starts with one
            $data = substr($data, 5);
        } else {
            // otherwise we need to add PHP closing tag at the beginning (it's probably HTML)
            $data = "?>$data";
        }
    }

    $matches = [];
    preg_match_all(
        '/((require|include)(_once)?)([( ]+)([\'"])([^\'"]+)\5[) ]*;/',
        $data,
        $matches,
    );

    foreach ($matches[6] as $k => $match) {
        if ($match === 'config.php') {
            continue;
        }
        $data = str_replace(
            $matches[0][$k],
            include_recursively(
                file_get_contents($base . '/' . $match),
                $level + 1,
                dirname($match)),
            $data,
        );
    }
    return $data;
}

echo include_recursively(file_get_contents('index.php'));
