<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <!-- Brand Logo -->
  <div class="sidebar">
    <div class="logo-leftside">
      <a href="{{ url('/') }}" class="brand-link">
        <div class="flex-shrink-0">
        <img src="{{ asset('dist/img/user2-160x160.jpg') }}" alt="User Avatar" class="img-size-60 rounded-circle me-3" />
        </div>
        
        <!--begin::Brand Text-->
        <h3 class="brand-text fw-light">{{ auth()->check() ? auth()->user()->name : 'Guest' }}</h3>
        <p>{{ auth()->check() ? auth()->user()->email : 'Guest' }}</p>
        <a href="{{ url('/profile') }}" class="nav-link {{ request()->is('profile') ? 'active' : '' }}">
          view profile
        </a>
        <!--end::Brand Text-->
      </a>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/projects') }}" class="nav-link {{ request()->is('projects*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-envelope"></i>
            <p>Projects</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/admin/users') }}" class="nav-link {{ request()->is('admin/users') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>Users</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/admin/permissions') }}" class="nav-link {{ request()->is('admin/permissions') ? 'active' : '' }}">
            <i class="nav-icon fas fa-key"></i>
            <p>Permissions</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/admin/roles') }}" class="nav-link {{ request()->is('admin/roles') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-tag"></i>
            <p>Roles</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ url('/suppliers') }}" class="nav-link {{ request()->is('suppliers*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user"></i>
            <p>Suppliers</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/drivers') }}" class="nav-link {{ request()->is('drivers*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-car"></i>
            <p>Drivers</p>
          </a>
        </li>
        @if(auth()->user()->hasAnyRole(['super-admin', 'approver']) || auth()->user()->can('po-receive'))
        <li class="nav-item">
          <a href="{{ route('portal-orders.index') }}" class="nav-link {{ request()->is('portal-orders*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-truck"></i>
            <p>Portal Orders</p>
          </a>
        </li>
        @endif
        <li class="nav-item">
          <a href="{{ url('/incoming-materials') }}" class="nav-link {{ request()->is('incoming-materials') ? 'active' : '' }}">
            <i class="nav-icon fas fa-warehouse"></i>
            <p>Incoming Materials</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/incoming-materials/filter-by-project') }}" class="nav-link {{ request()->is('incoming-materials/filter-by-project') ? 'active' : '' }}">
            <i class="nav-icon fas fa-dolly"></i>
            <p>Incoming Inventory</p>
          </a>
        </li>

        <li class="nav-item">
          <a href="{{ url('/outgoing-materials') }}" class="nav-link {{ request()->is('outgoing-materials') ? 'active' : '' }}">
            <i class="nav-icon fas fa-truck"></i>
            <p>Outgoing Materials</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/materials/transfer') }}" class="nav-link {{ request()->is('materials/transfer') ? 'active' : '' }}">
            <i class="nav-icon fas fa-exchange-alt"></i>
            <p>Transfer</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/return-materials') }}" class="nav-link {{ request()->is('return-materials*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-undo"></i>
            <p>Return Materials</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{route('tool-returns.log')}}" class="nav-link {{ request()->is('tool-returns/log') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tree"></i>
            <p>Tools Movement</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('tools.availability') }}" class="nav-link {{ request()->is('tools/availability') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tools"></i>
            <p>Tools Availability</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/tools') }}" class="nav-link {{ request()->is('tools') ? 'active' : '' }}">
            <i class="nav-icon far fa-plus-square"></i>
            <p>Add Tools</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/tool-assignments') }}" class="nav-link {{ request()->is('tool-assignments') ? 'active' : '' }}">
            <i class="nav-icon fas fa-columns"></i>
            <p>Asign Tools</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ url('/tool-returns') }}" class="nav-link {{ request()->is('tool-returns') ? 'active' : '' }}">
            <i class="nav-icon fas fa-book"></i>
            <p>Return Tools</p>
          </a>
        </li>
        @hasanyrole('super-admin|approver')
        <li class="nav-header">ADMIN</li>
        <li class="nav-item">
          <a href="{{ route('recycle-bin.index') }}" class="nav-link {{ request()->is('recycle-bin*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-recycle"></i>
            <p>Recycle Bin</p>
          </a>
        </li>
        @endhasanyrole
        <!-- Auth Links -->
        @if (auth()->check())
            <!-- If logged in -->
            <li class="nav-item">
                <a href="{{ route('logout') }}" class="nav-link"
                  onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <p>Logout</p>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </li>
        @else
            <!-- If not logged in -->
            <li class="nav-item">
                <a href="{{ route('login') }}" class="nav-link">
                    <i class="nav-icon fas fa-sign-in-alt"></i>
                    <p>Login</p>
                </a>
            </li>
        @endif

      </ul>
    </nav>
    <!-- /.sidebar-menu -->
  </div>
  <!-- /.sidebar -->
</aside>
