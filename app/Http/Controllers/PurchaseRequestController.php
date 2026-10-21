<?php

namespace App\Http\Controllers;

use App\Exceptions\DomainException;
use App\Http\Requests\PurchaseRequestDeliverRequest;
use App\Http\Requests\PurchaseRequestStoreRequest;
use App\Models\PurchaseRequest;
use App\Services\PurchaseRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function __construct(private readonly PurchaseRequestService $purchaseRequests) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = (string) $request->query('status', 'all');

        $query = PurchaseRequest::with(['user', 'items.product'])
            ->withCount('items')
            ->orderByDesc('id');

        if (in_array($status, [PurchaseRequest::STATUS_PENDING, PurchaseRequest::STATUS_PARTIAL, PurchaseRequest::STATUS_DELIVERED], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->whereHas('items.product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(10)->withQueryString();

        $pendingCount = PurchaseRequest::where('status', PurchaseRequest::STATUS_PENDING)->count();
        $partialCount = PurchaseRequest::where('status', PurchaseRequest::STATUS_PARTIAL)->count();
        $deliveredCount = PurchaseRequest::where('status', PurchaseRequest::STATUS_DELIVERED)->count();

        return view('solicitacoes.index', compact('requests', 'search', 'status', 'pendingCount', 'partialCount', 'deliveredCount'));
    }

    public function store(PurchaseRequestStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        try {
            $this->purchaseRequests->generate($data['note'] ?? null, $data['quantities'] ?? []);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('solicitacoes.index')->with('status', 'Solicitação de compra criada com sucesso.');
    }

    public function show(PurchaseRequest $purchaseRequest): View
    {
        $purchaseRequest->load(['user', 'deliveredBy', 'items.product.category', 'items.product.unitModel']);

        return view('solicitacoes.show', compact('purchaseRequest'));
    }

    public function deliver(PurchaseRequestDeliverRequest $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        try {
            $partial = $this->purchaseRequests->deliver(
                $purchaseRequest,
                $request->has('received') ? ($request->validated('received') ?? []) : null
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($partial) {
            return redirect()->route('solicitacoes.show', $purchaseRequest)->with('status', 'Recebimento parcial registrado. Itens faltantes continuam pendentes.');
        }

        return redirect()->route('solicitacoes.index')->with('status', 'Material recebido. Estoque atualizado automaticamente.');
    }

    public function destroy(PurchaseRequest $purchaseRequest): RedirectResponse
    {
        try {
            $this->purchaseRequests->destroy($purchaseRequest);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('solicitacoes.index')->with('status', 'Solicitação excluída.');
    }
}
