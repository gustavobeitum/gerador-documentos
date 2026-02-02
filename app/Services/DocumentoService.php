<?php

namespace App\Services;

use App\Models\Projeto;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Log;

class DocumentoService
{
    public function gerarDocumento(int $id): string
    {
        try {
            // 1. Busca os dados
            $projeto = Projeto::with('requisitos')->findOrFail($id);

            // 2. Define o caminho do template
            $templatePath = storage_path('app/templates/template.docx');

            if (!file_exists($templatePath)) {
                Log::error("Template não encontrado em: " . $templatePath);
                throw new \Exception("Arquivo template.docx não encontrado.");
            }

            // 3. Inicializa o processador
            $template = new TemplateProcessor($templatePath);

            // 4. Preenche os dados básicos com validação
            $template->setValue('titulo_projeto', $projeto->titulo ?? 'Título não informado');
            $template->setValue('descricao', $projeto->descricao ?? 'Descrição não informada');

            // 5. Processa Requisitos Funcionais
            $funcionais = $projeto->requisitos->where('tipo', 'funcional');

            if ($funcionais->isEmpty()) {
                $template->cloneRow('requisitos_f', 1);
                $template->setValue('requisitos_f#1', 'Nenhum requisito funcional cadastrado.');
            } else {
                $template->cloneRow('requisitos_f', $funcionais->count());
                foreach ($funcionais->values() as $index => $req) {
                    $template->setValue('requisitos_f#' . ($index + 1),
                        ($req->codigo ?? 'N/A') . " - " . ($req->descricao ?? 'Sem descrição'));
                }
            }

            // 6. Processa Requisitos Não Funcionais
            $naoFuncionais = $projeto->requisitos->where('tipo', 'nao-funcional');

            if ($naoFuncionais->isEmpty()) {
                $template->cloneRow('requisitos_nf', 1);
                $template->setValue('requisitos_nf#1', 'Nenhum requisito não funcional cadastrado.');
            } else {
                $template->cloneRow('requisitos_nf', $naoFuncionais->count());
                foreach ($naoFuncionais->values() as $index => $req) {
                    $template->setValue('requisitos_nf#' . ($index + 1),
                        ($req->codigo ?? 'N/A') . " - " . ($req->descricao ?? 'Sem descrição'));
                }
            }

            // 7. Salva o resultado
            $savePath = storage_path('app/public/doc_projeto_' . $id . '.docx');
            $publicDir = dirname($savePath);

            if (!file_exists($publicDir)) {
                if (!mkdir($publicDir, 0755, true)) {
                    throw new \Exception("Não foi possível criar o diretório: " . $publicDir);
                }
            }

            $template->saveAs($savePath);

            return $savePath;

        } catch (\Exception $e) {
            Log::error("Erro ao gerar documento para projeto {$id}: " . $e->getMessage());
            throw new \Exception("Erro ao gerar documento: " . $e->getMessage());
        }
    }
}
