<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;

class LegalController extends Controller
{
    public function privacy()
    {
        $document = LegalDocument::where('slug', 'privacy')
            ->where('is_active', true)
            ->first();

        if (!$document) {
            abort(404, 'Politique de confidentialité non trouvée');
        }

        // $document->content est DÉJÀ un tableau grâce au cast
        $contentArray = $document->content;

        // Convertir TipTap en HTML
        $htmlContent = $this->tiptapToHtml($contentArray);

        return view('legal.privacy-static', [
            'title' => $document->title,
            'htmlContent' => $htmlContent,
            'updated_at' => $document->updated_at,
        ]);
    }

    /**
     * Convertit le JSON TipTap en HTML
     */
    private function tiptapToHtml($node)
    {
        if (!is_array($node)) {
            return '';
        }

        $html = '';

        // Si c'est le document racine
        if (isset($node['type']) && $node['type'] === 'doc') {
            foreach ($node['content'] ?? [] as $child) {
                $html .= $this->tiptapToHtml($child);
            }
            return $html;
        }

        // Traite chaque type de nœud
        switch ($node['type'] ?? '') {
            case 'heading':
                $level = $node['attrs']['level'] ?? 2;
                $content = $this->getTextContent($node['content'] ?? []);
                return "<h{$level}>{$content}</h{$level}>";

            case 'paragraph':
                $content = $this->getTextContent($node['content'] ?? []);
                return "<p>{$content}</p>";

            case 'text':
                $text = htmlspecialchars($node['text'] ?? '');
                // Gérer le formatage (gras, italique, etc.)
                if (isset($node['marks']) && is_array($node['marks'])) {
                    foreach ($node['marks'] as $mark) {
                        if ($mark['type'] === 'bold') {
                            $text = "<strong>{$text}</strong>";
                        }
                        if ($mark['type'] === 'italic') {
                            $text = "<em>{$text}</em>";
                        }
                        if ($mark['type'] === 'link') {
                            $href = $mark['attrs']['href'] ?? '#';
                            $text = "<a href='{$href}'>{$text}</a>";
                        }
                    }
                }
                return $text;

            case 'listItem':
                $content = $this->getTextContent($node['content'] ?? []);
                return "<li>{$content}</li>";

            case 'bulletList':
                $items = '';
                foreach ($node['content'] ?? [] as $item) {
                    $items .= $this->tiptapToHtml($item);
                }
                return "<ul>{$items}</ul>";

            case 'orderedList':
                $items = '';
                foreach ($node['content'] ?? [] as $item) {
                    $items .= $this->tiptapToHtml($item);
                }
                return "<ol>{$items}</ol>";

            case 'horizontalRule':
                return "<hr>";

            case 'image':
                $src = $node['attrs']['src'] ?? '';
                $alt = $node['attrs']['alt'] ?? '';
                return "<img src='{$src}' alt='{$alt}' style='max-width:100%'>";

            case 'blockquote':
                $content = $this->getTextContent($node['content'] ?? []);
                return "<blockquote>{$content}</blockquote>";

            case 'codeBlock':
                $content = $this->getTextContent($node['content'] ?? []);
                return "<pre><code>{$content}</code></pre>";

            case 'hardBreak':
                return "<br>";

            default:
                // Pour les nœuds inconnus, on récupère le texte
                return $this->getTextContent($node['content'] ?? []);
        }
    }

    /**
     * Extrait le texte des enfants d'un nœud
     */
    private function getTextContent($nodes)
    {
        if (!is_array($nodes)) {
            return '';
        }

        $html = '';
        foreach ($nodes as $node) {
            $html .= $this->tiptapToHtml($node);
        }
        return $html;
    }
}
