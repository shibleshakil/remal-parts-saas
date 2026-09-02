@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </li>
        @endforeach
    </ul>
</div>
@endif
@if ($message = Session::get('success'))
<div class="alert alert-success alert-block">
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">×</span>
</button>
<strong class="text-white">{{ $message }}</strong>
</div>
@endif
@if ($message = Session::get('payment_success'))
<div class="alert alert-success alert-block">
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">×</span>
</button>
<strong class="text-white">{{ $message }}</strong>
</div>
@endif
@if ($message = Session::get('error'))
<div class="alert alert-danger alert-block">
<button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">×</span>
</button>
<strong class="text-white">{{ $message }}</strong>
</div>
@endif
