<?php

namespace Views\Core; // Or App\Views, App\Core, etc. Adjust namespace and composer.json

class View
{
    /**
     * Render a view file
     *
     * @param string $view The view file (e.g., "Home/index.php" for Views/Home/index.php)
     * @param array $args Associative array of data to display in the view (optional)
     *
     * @return string The rendered view content
     * @throws \Exception if view file not found
     */
    public static function render(string $view, array $args = []): string
    {
        if (!is_file(__DIR__ . '/../../Views/' . $view)) {
            throw new \RuntimeException("View template not found: {$view}");
        }

        extract($args);

        ob_start();
        include __DIR__ . '/../../Views/' . $view;
        return ob_get_clean();
    }
}
?>
