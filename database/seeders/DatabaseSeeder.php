<?php

namespace Database\Seeders;

use App\Models\Projeto;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Criando o projeto principal
        $projeto = Projeto::create([
            'titulo' => 'ArenaQuiz',
            'descricao' => 'O projeto ArenaQuiz visa a gamificação de quizzes com interação em diversos contextos, tais como palestras, cursos, treinamentos de equipes para empresas ou ambientes acadêmicos.'
        ]);

        // 2. Requisitos Funcionais (RF)
        $funcionais = [
            ['tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o administrador entre na área de jogo de qualquer cliente.'],
            ['tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o administrador visualize o relatório de clientes.'],
            ['tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o cliente faça a aquisição de um plano.'],
            ['tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o cliente com aquisição de um plano crie uma arena.'],
            ['tipo' => 'funcional', 'descricao' => 'O sistema deve permitir que o cliente com aquisição de um plano crie uma área.'],
        ];

        // 3. Requisitos Não Funcionais (RNF)
        $naoFuncionais = [
            ['tipo' => 'nao-funcional', 'descricao' => 'O sistema deve ser responsivo para funcionar em dispositivos móveis e desktop.'],
            ['tipo' => 'nao-funcional', 'descricao' => 'O sistema deve processar as respostas dos quizzes em tempo real (baixa latência).'],
            ['tipo' => 'nao-funcional', 'descricao' => 'O armazenamento de dados deve seguir as normas da LGPD.'],
        ];

        // 4. Inserindo os Requisitos no banco
        foreach (array_merge($funcionais, $naoFuncionais) as $requisito) {
            $projeto->requisitos()->create($requisito);
        }

        // 5. Inserindo Múltiplos Diagramas para teste da Seção 3

        // Diagrama 1: Com imagem (Testa o 3.1)
        $projeto->diagramas()->create([
            'tipo' => 'Diagrama de Caso de Uso',
            'caminho_imagem' => 'testes/caso_de_uso.jpeg' // Certifique-se que este arquivo existe
        ]);

        // Diagrama 2: Com imagem diferente (Testa o 3.2)
        $projeto->diagramas()->create([
            'tipo' => 'Diagrama de Classes',
            'caminho_imagem' => 'testes/caso_de_uso.jpeg' // Use outra imagem para validar a repetição
        ]);

        // Diagrama 3: SEM imagem (Testa o 3.3 apenas com título)
        $projeto->diagramas()->create([
            'tipo' => 'Diagrama de Sequência (Protótipo em andamento)',
            'caminho_imagem' => null // Testa a lógica de exibir apenas o nome
        ]);
    }
}
