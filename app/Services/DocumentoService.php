<?php

namespace App\Services;

use App\Models\Projeto;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentoService
{
    public function gerarDocumento(int $id): string
    {
        try {
            $projeto = Projeto::with(['requisitos', 'diagramas'])->findOrFail($id);
            $templatePath = storage_path('app/templates/template.docx');

            if (!file_exists($templatePath)) {
                throw new \Exception("Arquivo template.docx não encontrado.");
            }

            $template = new TemplateProcessor($templatePath);

            // Dados básicos
            $template->setValue('titulo_projeto', $projeto->titulo ?? 'Título não informado');
            $template->setValue('descricao', $projeto->descricao ?? 'Descrição não informada');

            // Processar Requisitos
            $this->processarRequisitos($template, $projeto);

            // Processar Diagramas (Imagens)
            $diagramas = $projeto->diagramas;
            if ($diagramas->isEmpty()) {
                $template->cloneBlock('bloco_diagramas', 0);
            } else {
                $template->cloneBlock('bloco_diagramas', $diagramas->count(), true, true);
                foreach ($diagramas as $index => $diag) {
                    $pos = $index + 1;

                    $template->setValue('tipo_diagrama#' . $pos, $diag->tipo);

                    $origem = $diag->caminho_imagem;
                    $tempPath = null;

                    try {
                        // Verifica se é uma URL externa
                        if (filter_var($origem, FILTER_VALIDATE_URL)) {
                            // Se for URL, baixa temporariamente
                            $conteudo = file_get_contents($origem);
                            $tempPath = tempnam(sys_get_temp_dir(), 'img_');
                            file_put_contents($tempPath, $conteudo);
                            $pathFinal = $tempPath;
                        } else {
                            // Se for local, usa o caminho do storage
                            $pathFinal = storage_path('app/public/' . $origem);
                        }

                        if (file_exists($pathFinal)) {
                            $template->setImageValue('img_diagrama#' . $pos, [
                                'path' => $pathFinal,
                                'width' => 600,
                                'height' => 900,
                                'ratio' => true
                            ]);
                        }

                        // Deleta o temporário se ele foi criado
                        if ($tempPath && file_exists($tempPath)) unlink($tempPath);

                    } catch (\Exception $e) {
                        $template->setValue('img_diagrama#' . $pos, 'Erro ao carregar imagem.');
                    }
                }
            }

            $nomeArquivo = Str::slug($projeto->titulo ?? 'documento');
            $savePath =  storage_path('app/public/') . '/doc_projeto_' . $nomeArquivo . '.docx';
            $template->saveAs($savePath);

            return $savePath;

        } catch (\Exception $e) {
            Log::error("Erro ao gerar documento: " . $e->getMessage());
            throw $e;
        }
    }

    private function processarRequisitos($template, $projeto)
    {
        foreach (['funcional' => 'requisitos_f', 'nao-funcional' => 'requisitos_nf'] as $tipo => $placeholder) {
            $reqs = $projeto->requisitos->where('tipo', $tipo);
            if ($reqs->isEmpty()) {
                $template->cloneRow($placeholder, 1);
                $template->setValue($placeholder . '#1', "Nenhum requisito {$tipo} cadastrado.");
            } else {
                $template->cloneRow($placeholder, $reqs->count());
                foreach ($reqs->values() as $index => $req) {
                    $template->setValue($placeholder . '#' . ($index + 1), "{$req->codigo} - {$req->descricao}");
                }
            }
        }
    }
}
