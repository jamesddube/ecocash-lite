<form action="" method="POST">
    @foreach($params as $paramName => $paramValue)
        <input type="hidden" name="{{ $paramName }}" value="{{ $paramValue }}" />
    @endforeach
    <input type="hidden" name="gateway" value="ECOCASHLITE" />
    <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
    <input type="submit" value="Pay with EcoCash" />
</form>