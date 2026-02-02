<?php

namespace App\Http\Controllers;

use App\Services\DocumentoService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentoController extends Controller
{
    public function __construct(
        protected DocumentoService $service
    ) {}

    public function show(int $id): BinaryFileResponse
    {
        $caminho = $this->service->gerarDocumento($id);

        return response()
            ->download($caminho)
            ->deleteFileAfterSend(true);
    }
}
