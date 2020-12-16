<!-- Sidebar navigation-->
<nav class="sidebar-nav">
    <ul id="sidebarnav">
        <li class="user-pro"> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><span class="hide-menu">@if (Auth::user())
            {{Auth::user()->nama}} 
            @else 
            MASUK 
            @endif </span></a>
            <ul aria-expanded="false" class="collapse">
                @if (Auth::user())
                <li><a href="javascript:void(0)"><i class="ti-settings"></i> Ganti Password</a></li>
                <li><a href="{{route('logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
                @else 
                <li><a href="{{route('login')}}"><i class="fa fa-power-off"></i> Login</a></li>
                @endif
            </ul>
        </li>
        <li class="nav-small-cap">--- DEPAN</li>
        <li> <a class="active waves-effect waves-dark" href="{{url('')}}" aria-expanded="false"><i class="icon-speedometer"></i><span class="hide-menu">DASHBOARD</span></a>
        </li>
        @if (Auth::user())
        <li class="nav-small-cap">--- ADMIN</li>  
        <li>
            <a class="waves-effect waves-dark" href="{{route('pengunjung.list')}}" aria-expanded="false"><i class="ti-pie-chart"></i><span class="hide-menu">PENGUNJUNG</span></a>
        </li>      
        <li> <a class="waves-effect waves-dark" href="{{route('konsultasi.list')}}" aria-expanded="false"><i class="ti-pie-chart"></i><span class="hide-menu">KONSULTASI</span></a>
        </li>
        <li> <a class="waves-effect waves-dark" href="{{route('cari.list')}}" aria-expanded="false"><i class="ti-pie-chart"></i><span class="hide-menu">LOG PENCARIAN</span></a>
        </li>
        <li> <a class="waves-effect waves-dark" href="{{route('feedback.list')}}" aria-expanded="false"><i class="ti-pie-chart"></i><span class="hide-menu">FEEDBACK</span></a>
        </li>
        <li> <a class="waves-effect waves-dark" href="{{route('admin.list')}}" aria-expanded="false"><i class="ti-layout-grid2"></i><span class="hide-menu">USER</span></a>
        </li>
            @if (Auth::user()->username=='admin')
                <li> <a class="waves-effect waves-dark" href="{{route('set.webhook')}}" aria-expanded="false"><i class="ti-layout-grid2"></i><span class="hide-menu">SET WEBHOOK</span></a>
                </li>
                <li> <a class="waves-effect waves-dark" href="{{route('bot.status')}}" aria-expanded="false"><i class="ti-layout-grid2"></i><span class="hide-menu">BOT STATUS</span></a>
                </li>
                <li> <a class="waves-effect waves-dark" href="{{route('get.me')}}" aria-expanded="false"><i class="ti-layout-grid2"></i><span class="hide-menu">GET ME</span></a>
                </li>
            @endif
        @endif
    </ul>
</nav>
<!-- End Sidebar navigation -->