@extends('layout.app')

@section('content')
    <div class="container mx-auto">

        <ladder :league="{{ $leagueName }}"></ladder>
    </div>
@endsection
