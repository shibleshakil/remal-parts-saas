@php
    use App\Models\Balance;
    $balance = Balance::withoutGlobalScopes()
                            ->firstOrCreate(
                                ['branch_id' => active_branch_id()],
                                ['balance' => 0]
                            );
@endphp

<nav class="header-navbar navbar-expand-md navbar navbar-with-menu fixed-top navbar-semi-dark navbar-shadow">
    <div class="navbar-wrapper">
        <div class="navbar-header">
            <ul class="nav navbar-nav flex-row">
                <li class="nav-item mobile-menu d-md-none mr-auto"><a class="nav-link nav-menu-main menu-toggle hidden-xs"
                        href="#"><i class="feather icon-menu font-large-1"></i></a></li>
                <li class="nav-item">
                    <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                        <img src="{{ asset('public/app-assets/images/logo/remalalwafi.png') }}" alt="logo"
                            style="height:25px; width:auto;">
                        <h4 class="brand-text m-0">REMAL AL WAFI</h4>
                    </a>
                </li>
                <li class="nav-item d-md-none"><a class="nav-link open-navbar-container" data-toggle="collapse"
                        data-target="#navbar-mobile"><i class="fa fa-ellipsis-v"></i></a></li>
            </ul>
        </div>


        <div class="navbar-container content">
            <div class="collapse navbar-collapse" id="navbar-mobile">
                <ul class="nav navbar-nav mr-auto float-left">
                    <li class="nav-item d-none d-md-block"><a class="nav-link nav-menu-main menu-toggle hidden-xs"
                            href="#"><i class="feather icon-menu"></i></a>
                    </li>
                </ul>
                @auth
                    <ul class="nav navbar-nav float-right">
                        <li class="nav-item d-flex align-items-center justify-content-center">
                            <span class="font-weight-bolder pr-5">Account Balance: <span
                                    id="account_balance">{{ number_format($balance->balance, 2) }}</span></span>
                        </li>
                        @if(isSuperAdmin())
                        <li class="form-group mb-0 d-flex align-items-center justify-content-center">
                            <form method="POST" action="{{ route('branch.switch') }}">
                                @csrf
                                <select class="form-control" name="branch_id" onchange="this.form.submit()">
                                    @foreach(branches() as $branch)
                                        <option value="{{ $branch->id }}"
                                            {{ session('active_branch_id', auth()->user()->branch_id) == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </li>
                        @else
                        <li class="form-group mb-0 d-flex align-items-center justify-content-center">
                            <span class="font-weight-bolder border border p-1 rounded">
                                {{ branches()->where('id', session('active_branch_id', auth()->user()->branch_id))->first()->name}}
                            </span>
                        </li>
                        @endif
                        <li class="dropdown dropdown-user nav-item"><a class="dropdown-toggle nav-link dropdown-user-link"
                                href="#" data-toggle="dropdown">
                                <div class="avatar avatar-online"><img
                                        src="{{ auth()->user()->image
                                            ? asset('public/storage/' . auth()->user()->image)
                                            : asset('public/app-assets/images/portrait/small/avatar-s-1.png') }}"
                                        alt="avatar"><i></i></div><span
                                    class="user-name">{{ auth()->user()->name }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('profile', [\Str::slug(auth()->user()->name)]) }}">
                                    <i class="feather icon-user"></i> Edit Profile
                                </a>
                                <a class="dropdown-item" href="{{ route('password.update') }}">
                                    <i class="feather icon-lock"></i> Change Password
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();"><i
                                        class="feather icon-log-out"></i>{{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    </ul>
                @endauth
            </div>
        </div>
    </div>
</nav>
