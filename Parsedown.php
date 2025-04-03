<?php
# https://github.com/erusev/parsedown
# Version abrégée de Parsedown pour ce projet

class Parsedown {
    function text($text) {
        return '<p>' . htmlspecialchars($text) . '</p>'; // Remplacer par version complète si besoin
    }
}
