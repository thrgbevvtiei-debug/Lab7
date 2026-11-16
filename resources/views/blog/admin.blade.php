@extends('layouts.app')

@section('title', 'Адміністрування блогу')

@section('content')
    @php
        // Посилання для сортування: повторний клік міняє напрямок, page скидається
        $currentSort = request('sort');
        $sortLink = function (string $field, string $label) use ($currentSort) {
            $next  = $currentSort === $field ? '-' . $field : $field;
            $arrow = $currentSort === $field ? ' ↑' : ($currentSort === '-' . $field ? ' ↓' : '');
            $url   = route('blog.admin', array_merge(request()->except('sort', 'page'), ['sort' => $next]));

            return '<a href="' . e($url) . '" class="text-white text-decoration-none">' . e($label . $arrow) . '</a>';
        };
    @endphp

    <h1>Адміністрування блогу</h1>
    <p><a href="{{ route('blog.create') }}">Додати запис</a></p>

    <form method="GET" action="{{ route('blog.admin') }}" class="row g-2 mb-3">
        <div class="col-md-3">
            <label for="title" class="form-label small mb-0">Заголовок</label>
            <input type="text" id="title" name="title" class="form-control"
                   value="{{ request('title') }}" placeholder="Пошук за заголовком">
        </div>
        <div class="col-md-3">
            <label for="categoryName" class="form-label small mb-0">Назва категорії</label>
            <input type="text" id="categoryName" name="categoryName" class="form-control"
                   value="{{ request('categoryName') }}" placeholder="Назва категорії">
        </div>
        <div class="col-md-2">
            <label for="category_id" class="form-label small mb-0">Категорія</label>
            <select id="category_id" name="category_id" class="form-select" onchange="this.form.submit()">
                <option value="">Усі категорії</option>
                @foreach ($categories as $id => $name)
                    <option value="{{ $id }}" @selected(request('category_id') == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="filter_id" class="form-label small mb-0">Мітка</label>
            <select id="filter_id" name="filter_id" class="form-select" onchange="this.form.submit()">
                <option value="">Усі мітки</option>
                @foreach ($filters as $id => $name)
                    <option value="{{ $id }}" @selected(request('filter_id') == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-secondary">Застосувати</button>
            <a href="{{ route('blog.admin') }}" class="btn btn-outline-secondary">Скинути</a>
        </div>
    </form>

    <table class="table table-striped border align-middle">
        <thead class="table-dark">
        <tr>
            <th>№</th>
            <th>{!! $sortLink('title', 'Заголовок') !!}</th>
            <th>{!! $sortLink('categoryName', 'Категорія') !!}</th>
            <th>Мітки</th>
            <th>{!! $sortLink('created', 'Створено') !!}</th>
            <th>Дії</th>
        </tr>
        </thead>
        <tbody>
        @forelse ($blogs as $i => $blog)
            <tr>
                <td>{{ $blogs->firstItem() + $i }}</td>
                <td><a href="{{ route('blog.show', $blog) }}">{{ $blog->t_title }}</a></td>
                <td>{{ $blog->category?->category_name ?? '—' }}</td>
                <td>
                    @forelse ($blog->filters as $filter)
                        <span class="badge bg-secondary">{{ $filter->filter_name }}</span>
                    @empty
                        —
                    @endforelse
                </td>
                <td>{{ $blog->dt_created->format('d.m.Y') }}</td>
                <td class="d-flex gap-2">
                    <a href="{{ route('blog.edit', $blog) }}">Редагувати</a>
                    <form method="POST" action="{{ route('blog.destroy', $blog) }}"
                          onsubmit="return confirm('Видалити запис?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Видалити</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted">Записів не знайдено.</td></tr>
        @endforelse
        </tbody>
    </table>

    {{ $blogs->links() }}
@endsection
