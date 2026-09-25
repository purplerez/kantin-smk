<?php

namespace App\Livewire\Tenant;

use App\Models\Category;
use App\Models\Product;
use App\Services\AuditLogger;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.panel')]
#[Title('Kelola Menu — Tenant')]
class Products extends Component
{
    use AuthorizesRequests;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $description = '';

    public ?int $price = null;

    public ?int $category_id = null;

    public string $image_url = '';

    public bool $is_available_today = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'price' => ['required', 'integer', 'min:500', 'max:1000000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'is_available_today' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->authorize('create', Product::class);
        $this->reset('editingId', 'name', 'description', 'price', 'category_id', 'image_url');
        $this->is_available_today = true;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('update', $product);

        $this->editingId = $product->id;
        $this->fill($product->only('name', 'description', 'price', 'category_id', 'image_url', 'is_available_today'));
        $this->description ??= '';
        $this->image_url ??= '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            $product = Product::findOrFail($this->editingId);
            $this->authorize('update', $product);
            $product->update($data);
            AuditLogger::log('product.updated', $product, $data);
        } else {
            $this->authorize('create', Product::class);
            $product = Product::create($data);
            AuditLogger::log('product.created', $product, $data);
        }

        $this->showForm = false;
        $this->dispatch('toast', message: 'Menu tersimpan.');
    }

    public function toggleAvailability(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('toggleAvailability', $product);
        $product->update(['is_available_today' => ! $product->is_available_today]);
        AuditLogger::log('product.availability', $product, ['is_available_today' => $product->is_available_today]);
    }

    public function delete(int $id): void
    {
        $product = Product::findOrFail($id);
        $this->authorize('delete', $product);
        $product->delete();
        AuditLogger::log('product.deleted', $product);
        $this->dispatch('toast', message: 'Menu dihapus.');
    }

    public function render()
    {
        return view('livewire.tenant.products', [
            'products' => Product::with('category')
                ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
                ->orderBy('name')->get(),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }
}
