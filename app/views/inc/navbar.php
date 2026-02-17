<nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <a class="navbar-brand" href="/home"> BNGRC</a>
        <a class="navbar-brand brand-logo-mini" href="/home"><img src="assets/images/favicon.png"
                alt="logo" /></a>
    </div>
    <div class="navbar-menu-wrapper d-flex align-items-stretch">
        <ul class="navbar-nav ms-auto">
            <li class="nav-item d-none d-lg-block">
                <a class="nav-link d-flex align-items-center" href="/home">
                    <i class="fa fa-home me-2 text-black"></i>
                    <span>Accueil</span>
                </a>
            </li>

            <li class="nav-item d-none d-lg-block">
                <a class="nav-link d-flex align-items-center" href="/recap">
                    <i class="fa  fa-list-alt me-2 text-black"></i>
                    <span>Recap</span>
                </a>
            </li>

            <li class="nav-item d-none d-lg-block">
                <a class="nav-link d-flex align-items-center" href="/vente">
                    <i class="fa fa-send me-2 text-black"></i>
                    <span>Vente</span>
                </a>
            </li>

            <li class="nav-item d-none d-lg-block">
                <a class="nav-link d-flex align-items-center" href="/besoin/form">
                    <i class="fa fa-plus-circle me-2 text-black"></i>
                    <span>Ajouter un besoin</span>
                </a>
            </li>

            <!-- Dropdown: Faire un don -->
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="donDropdown"
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-inbox me-2 text-black"></i>Faire un don
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="donDropdown">
                    <li><a class="dropdown-item" href="/dons/sans-besoin">Ajouter un don</a></li>
                    <li><a class="dropdown-item" href="/liaison">Liaison de don</a></li>
                </ul>
            </li>

            <li class="nav-item d-none d-lg-block">
                <a class="nav-link d-flex align-items-center" href="/achat">
                    <i class="fa fa-archive me-2 text-black"></i>
                    <span>Effectuer un achat</span>
                </a>
            </li>

            <li class="nav-item d-none d-lg-block">
                <a class="nav-link d-flex align-items-center  text-danger" href="/reset">
                    <i class="fa  fa-warning me-2"></i>
                    <span>Réinitialiser</span>
                </a>
            </li>
        </ul>

        
    </div>
</nav>