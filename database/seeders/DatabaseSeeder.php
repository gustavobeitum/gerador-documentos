<?php

namespace Database\Seeders;

use App\Models\Projeto;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Criando o projeto principal
        $projeto = Projeto::create([
            'titulo' => 'ArenaQuiz',
            'descricao' => 'O projeto ArenaQuiz visa a gamificação de quizzes com interação em diversos contextos, tais como palestras, cursos, treinamentos de equipes para empresas ou ambientes acadêmicos.'
        ]);

        // Requisitos Funcionais (RF) baseados na documentação
        $funcionais = [
            ['codigo' => 'RF01', 'tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o administrador entre na área de jogo de qualquer cliente.'],
            ['codigo' => 'RF02', 'tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o administrador visualize o relatório de clientes.'],
            ['codigo' => 'RF07', 'tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o cliente faça a aquisição de um plano.'],
            ['codigo' => 'RF08', 'tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o cliente com aquisição de um plano crie uma arena.'],
            ['codigo' => 'RF10', 'tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o cliente com aquisição de um plano crie uma área.'],
        ];

        // Requisitos Não Funcionais (RNF) - Exemplos comuns para seu tipo de projeto
        $naoFuncionais = [
            ['codigo' => 'RNF01', 'tipo' => 'nao-funcional', 'descricao' => 'O sistema deve ser responsivo para funcionar em dispositivos móveis e desktop.'],
            ['codigo' => 'RNF02', 'tipo' => 'nao-funcional', 'descricao' => 'O sistema deve processar as respostas dos quizzes em tempo real (baixa latência).'],
            ['codigo' => 'RNF03', 'tipo' => 'nao-funcional', 'descricao' => 'O armazenamento de dados deve seguir as normas da LGPD.'],
        ];

        $projeto->diagramas()->create([
            'tipo' => 'Diagrama de Caso de Uso',
            'caminho_imagem' => 'testes/caso_de_uso.jpeg'
        ]);

        // Inserindo no banco
        foreach (array_merge($funcionais, $naoFuncionais) as $requisito) {
            $projeto->requisitos()->create($requisito);
        }
    }
}
