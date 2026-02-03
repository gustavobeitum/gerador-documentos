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
                //Remove o bloco inteiro se não houver diagramas registrados
                $template->cloneBlock('bloco_diagramas', 0);
            } else {
                //Clona o bloco para cada diagrama encontrado no banco
                $template->cloneBlock('bloco_diagramas', $diagramas->count(), true, true);

                foreach ($diagramas as $index => $diag) {
                    $pos = $index + 1;

                    //Injeta a subnumeração para formar o 3.1, 3.2...
                    $template->setValue('posicao_diagrama#' . $pos, $pos);

                    //Injeta o nome do diagrama
                    $template->setValue('tipo_diagrama#' . $pos, $diag->tipo);

                    $origem = $diag->caminho_imagem;
                    $pathFinal = storage_path('app/public/' . $origem);

                    //Se a imagem existir fisicamente, ela é inserida
                    if (!empty($origem) && file_exists($pathFinal)) {
                        $template->setImageValue('img_diagrama#' . $pos, [
                            'path'   => $pathFinal,
                            'width'  => 450,
                            'height' => 700,
                            'ratio'  => true
                        ]);
                    } else {
                        //Se não houver imagem, limpamos o placeholder para aparecer apenas o nome
                        $template->setValue('img_diagrama#' . $pos, '');
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
