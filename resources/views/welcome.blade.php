@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Welcome</div>

                <div class="panel-body">
                    Your Application's Landing Page.
                    {{ Form::open(array('url'=>'create-temporary-account','files'=>true)) }}

                    {{ Form::label('file','File',array('id'=>'','class'=>'')) }}
                    {{ Form::file('file','',array('id'=>'','class'=>'')) }}
                    <br/>
                    <!-- submit buttons -->
                    {{ Form::submit('Save') }}

                            <!-- reset buttons -->
                    {{ Form::reset('Reset') }}

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
