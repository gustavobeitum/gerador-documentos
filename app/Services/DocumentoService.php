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

            //Dados - Titulo e descrição
            $template->setValue('titulo_projeto', $projeto->titulo ?? 'Título não informado');
            $template->setValue('descricao', $projeto->descricao ?? 'Descrição não informada');

            //Processar Requisitos
            $this->processarRequisitos($template, $projeto);

            //Processar Diagramas (Imagens)
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
                        //Verifica se é uma URL externa
                        if (filter_var($origem, FILTER_VALIDATE_URL)) {
                            //Se for URL, baixa temporariamente
                            $conteudo = file_get_contents($origem);
                            $tempPath = tempnam(sys_get_temp_dir(), 'img_');
                            file_put_contents($tempPath, $conteudo);
                            $pathFinal = $tempPath;
                        } else {
                            //Se for local, usa o caminho do storage
                            $pathFinal = storage_path('app/public/' . $origem);
                        }

                        if (file_exists($pathFinal)) {
                            $template->setImageValue('img_diagrama#' . $pos, [
                                'path' => $pathFinal,
                                //Define tamanho da imagem
                                'width' => 600,
                                'height' => 900,
                                'ratio' => true
                            ]);
                        }

                        //Deleta o temporário se ele foi criado
                        if ($tempPath && file_exists($tempPath)) unlink($tempPath);

                    } catch (\Exception $e) {
                        $template->setValue('img_diagrama#' . $pos, 'Erro ao carregar imagem.');
                    }
                }
            }

            //Define título do aquivo
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
            $reqs = $projeto->requisitos->filter(function($req) use ($tipo) {
                return trim(strtolower($req->tipo)) === strtolower($tipo);
            });

            //Verifica se a lista de requisitos está vazia
            if ($reqs->isEmpty()) {
                //Cria uma linha padrão com mensagem de aviso
                $template->cloneRow($placeholder, 1);
                $template->setValue($placeholder . '#1', "Nenhum requisito {$tipo} cadastrado.");
            } else {
                //Clona a linha da tabela conforme a quantidade de requisitos encontrados
                $template->cloneRow($placeholder, $reqs->count());

                //Reindexamos os valores para garantir que o índice comece em 0
                foreach ($reqs->values() as $index => $req) {
                    $numero = $index + 1; // Começa em 1

                    //Define o prefixo: RF para Funcional, RNF para Não Funcional
                    $prefixo = ($tipo === 'funcional') ? 'RF' : 'RNF';

                    //Formata o número com dois dígitos
                    $codigoGerado = $prefixo . str_pad($numero, 2, '0', STR_PAD_LEFT);

                    //Injeta no Word RF01 - Descrição do requisito
                    $template->setValue(
                        $placeholder . '#' . $numero,
                        "{$codigoGerado} - " . ($req->descricao ?? 'Sem descrição')
                    );
                }
            }
        }
    }
}
