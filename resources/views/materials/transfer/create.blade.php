<!-- resources/views/materials/transfer/create.blade.php -->

@extends('layouts.app')

@section('content')
<section  class="content">

    <h1>Transfer Item: {{ $item->item_name }} to General</h1>
    
    <form action="{{ route('materials.transfer.store', $item) }}" method="POST">
        @csrf
        <div>
            <label for="quantity">Quantity to Transfer:</label>
            <input type="number" name="quantity" id="quantity" min="1" max="{{ $item->quantity }}" required>
        </div>
        <button type="submit">Transfer</button>
    </form>
</section>
@endsection
