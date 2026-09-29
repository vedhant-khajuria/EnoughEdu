(() => {
    'use strict';
    const U = (label, items) => ({
        label,
        units: Object.fromEntries(
            items.map(([key, name, factor, offset = 0]) => [key, { label: name, factor, offset }]),
        ),
    });
    window.ENOUGHEDU_UNITS = {
        length: U('Length', [
            ['m', 'metre (m)', 1],
            ['km', 'kilometre (km)', 1000],
            ['cm', 'centimetre (cm)', 0.01],
            ['mm', 'millimetre (mm)', 0.001],
            ['um', 'micrometre (µm)', 1e-6],
            ['nm', 'nanometre (nm)', 1e-9],
            ['in', 'inch (in)', 0.0254],
            ['ft', 'foot (ft)', 0.3048],
            ['yd', 'yard (yd)', 0.9144],
            ['mi', 'mile (mi)', 1609.344],
            ['nmi', 'nautical mile', 1852],
            ['angstrom', 'angstrom (Å)', 1e-10],
        ]),
        area: U('Area', [
            ['m2', 'square metre', 1],
            ['km2', 'square kilometre', 1e6],
            ['cm2', 'square centimetre', 1e-4],
            ['mm2', 'square millimetre', 1e-6],
            ['ha', 'hectare', 1e4],
            ['acre', 'acre', 4046.8564224],
            ['ft2', 'square foot', 0.09290304],
            ['in2', 'square inch', 0.00064516],
            ['yd2', 'square yard', 0.83612736],
        ]),
        volume: U('Volume', [
            ['m3', 'cubic metre', 1],
            ['L', 'litre', 0.001],
            ['mL', 'millilitre', 1e-6],
            ['cm3', 'cubic centimetre', 1e-6],
            ['mm3', 'cubic millimetre', 1e-9],
            ['ft3', 'cubic foot', 0.028316846592],
            ['in3', 'cubic inch', 0.000016387064],
            ['gal_us', 'US gallon', 0.003785411784],
            ['gal_uk', 'UK gallon', 0.00454609],
            ['bbl', 'oil barrel', 0.158987294928],
        ]),
        mass: U('Mass', [
            ['kg', 'kilogram', 1],
            ['g', 'gram', 0.001],
            ['mg', 'milligram', 1e-6],
            ['ug', 'microgram', 1e-9],
            ['t', 'metric tonne', 1000],
            ['lb', 'pound', 0.45359237],
            ['oz', 'ounce', 0.028349523125],
            ['slug', 'slug', 14.59390294],
            ['ton_us', 'US short ton', 907.18474],
            ['ton_uk', 'UK long ton', 1016.0469088],
        ]),
        time: U('Time', [
            ['s', 'second', 1],
            ['ms', 'millisecond', 0.001],
            ['us', 'microsecond', 1e-6],
            ['ns', 'nanosecond', 1e-9],
            ['min', 'minute', 60],
            ['h', 'hour', 3600],
            ['day', 'day', 86400],
            ['week', 'week', 604800],
            ['year', 'Julian year', 31557600],
        ]),
        speed: U('Speed', [
            ['mps', 'metre/second', 1],
            ['kmph', 'kilometre/hour', 0.2777777778],
            ['mph', 'mile/hour', 0.44704],
            ['fps', 'foot/second', 0.3048],
            ['knot', 'knot', 0.5144444444],
            ['mach', 'Mach (standard atmosphere)', 340.29],
            ['c', 'speed of light', 299792458],
        ]),
        acceleration: U('Acceleration', [
            ['mps2', 'metre/second²', 1],
            ['cmps2', 'centimetre/second²', 0.01],
            ['ftps2', 'foot/second²', 0.3048],
            ['gal', 'Gal', 0.01],
            ['g0', 'standard gravity', 9.80665],
        ]),
        force: U('Force', [
            ['N', 'newton', 1],
            ['kN', 'kilonewton', 1000],
            ['MN', 'meganewton', 1e6],
            ['dyn', 'dyne', 1e-5],
            ['kgf', 'kilogram-force', 9.80665],
            ['lbf', 'pound-force', 4.4482216153],
            ['kip', 'kip-force', 4448.2216153],
        ]),
        pressure: U('Pressure and stress', [
            ['Pa', 'pascal', 1],
            ['kPa', 'kilopascal', 1000],
            ['MPa', 'megapascal', 1e6],
            ['GPa', 'gigapascal', 1e9],
            ['bar', 'bar', 1e5],
            ['mbar', 'millibar', 100],
            ['atm', 'standard atmosphere', 101325],
            ['psi', 'pound/inch²', 6894.757293],
            ['ksi', 'kip/inch²', 6894757.293],
            ['torr', 'torr', 133.3223684],
            ['mmHg', 'millimetre of mercury', 133.3223874],
            ['inHg', 'inch of mercury', 3386.389],
        ]),
        energy: U('Energy and work', [
            ['J', 'joule', 1],
            ['kJ', 'kilojoule', 1000],
            ['MJ', 'megajoule', 1e6],
            ['Wh', 'watt-hour', 3600],
            ['kWh', 'kilowatt-hour', 3.6e6],
            ['cal', 'thermochemical calorie', 4.184],
            ['kcal', 'kilocalorie', 4184],
            ['BTU', 'BTU (IT)', 1055.055853],
            ['eV', 'electronvolt', 1.602176634e-19],
            ['ftlb', 'foot-pound force', 1.3558179483],
            ['erg', 'erg', 1e-7],
            ['toe', 'tonne oil equivalent', 4.1868e10],
        ]),
        power: U('Power', [
            ['W', 'watt', 1],
            ['kW', 'kilowatt', 1000],
            ['MW', 'megawatt', 1e6],
            ['GW', 'gigawatt', 1e9],
            ['hp', 'mechanical horsepower', 745.6998716],
            ['hp_metric', 'metric horsepower', 735.49875],
            ['BTUph', 'BTU/hour', 0.29307107],
            ['ftlbps', 'foot-pound/second', 1.3558179483],
        ]),
        torque: U('Torque', [
            ['Nm', 'newton-metre', 1],
            ['kNm', 'kilonewton-metre', 1000],
            ['Nmm', 'newton-millimetre', 0.001],
            ['kgfm', 'kilogram-force metre', 9.80665],
            ['lbfft', 'pound-force foot', 1.3558179483],
            ['lbfin', 'pound-force inch', 0.112984829],
        ]),
        temperature: U('Temperature', [
            ['K', 'kelvin', 1, 0],
            ['C', 'degree Celsius', 1, 273.15],
            ['F', 'degree Fahrenheit', 5 / 9, 459.67],
            ['R', 'degree Rankine', 5 / 9, 0],
        ]),
        angle: U('Plane angle', [
            ['rad', 'radian', 1],
            ['deg', 'degree', Math.PI / 180],
            ['grad', 'gradian', Math.PI / 200],
            ['rev', 'revolution', 2 * Math.PI],
            ['arcmin', 'arcminute', Math.PI / 10800],
            ['arcsec', 'arcsecond', Math.PI / 648000],
        ]),
        frequency: U('Frequency', [
            ['Hz', 'hertz', 1],
            ['kHz', 'kilohertz', 1e3],
            ['MHz', 'megahertz', 1e6],
            ['GHz', 'gigahertz', 1e9],
            ['rpm', 'revolution/minute', 1 / 60],
            ['radps', 'radian/second', 1 / (2 * Math.PI)],
        ]),
        density: U('Density', [
            ['kgm3', 'kilogram/metre³', 1],
            ['gcm3', 'gram/centimetre³', 1000],
            ['kgL', 'kilogram/litre', 1000],
            ['lbft3', 'pound/foot³', 16.01846337],
            ['lbin3', 'pound/inch³', 27679.90471],
            ['slugft3', 'slug/foot³', 515.3788184],
        ]),
        flow_volume: U('Volumetric flow rate', [
            ['m3s', 'metre³/second', 1],
            ['m3h', 'metre³/hour', 1 / 3600],
            ['Ls', 'litre/second', 0.001],
            ['Lmin', 'litre/minute', 1 / 60000],
            ['cfm', 'cubic foot/minute', 0.00047194745],
            ['cfs', 'cubic foot/second', 0.0283168466],
            ['gpm_us', 'US gallon/minute', 0.000063090196],
            ['gpm_uk', 'UK gallon/minute', 0.000075768167],
        ]),
        flow_mass: U('Mass flow rate', [
            ['kgs', 'kilogram/second', 1],
            ['kgh', 'kilogram/hour', 1 / 3600],
            ['gs', 'gram/second', 0.001],
            ['th', 'tonne/hour', 1000 / 3600],
            ['lbsh', 'pound/hour', 0.45359237 / 3600],
            ['lbsmin', 'pound/minute', 0.45359237 / 60],
        ]),
        viscosity_dynamic: U('Dynamic viscosity', [
            ['Pas', 'pascal-second', 1],
            ['mPas', 'millipascal-second', 0.001],
            ['P', 'poise', 0.1],
            ['cP', 'centipoise', 0.001],
            ['lbfts', 'pound/(foot·second)', 1.4881639436],
        ]),
        viscosity_kinematic: U('Kinematic viscosity', [
            ['m2s', 'metre²/second', 1],
            ['mm2s', 'millimetre²/second', 1e-6],
            ['St', 'stokes', 1e-4],
            ['cSt', 'centistokes', 1e-6],
            ['ft2s', 'foot²/second', 0.09290304],
        ]),
        charge: U('Electric charge', [
            ['C', 'coulomb', 1],
            ['mC', 'millicoulomb', 1e-3],
            ['uC', 'microcoulomb', 1e-6],
            ['nC', 'nanocoulomb', 1e-9],
            ['Ah', 'ampere-hour', 3600],
            ['mAh', 'milliampere-hour', 3.6],
            ['e', 'elementary charge', 1.602176634e-19],
        ]),
        current: U('Electric current', [
            ['A', 'ampere', 1],
            ['mA', 'milliampere', 1e-3],
            ['uA', 'microampere', 1e-6],
            ['nA', 'nanoampere', 1e-9],
            ['kA', 'kiloampere', 1e3],
        ]),
        voltage: U('Electric potential', [
            ['V', 'volt', 1],
            ['mV', 'millivolt', 1e-3],
            ['uV', 'microvolt', 1e-6],
            ['kV', 'kilovolt', 1e3],
            ['MV', 'megavolt', 1e6],
        ]),
        resistance: U('Electrical resistance', [
            ['ohm', 'ohm', 1],
            ['mohm', 'milliohm', 1e-3],
            ['kohm', 'kiloohm', 1e3],
            ['Mohm', 'megaohm', 1e6],
            ['Gohm', 'gigaohm', 1e9],
        ]),
        conductance: U('Electrical conductance', [
            ['S', 'siemens', 1],
            ['mS', 'millisiemens', 1e-3],
            ['uS', 'microsiemens', 1e-6],
            ['kS', 'kilosiemens', 1e3],
        ]),
        capacitance: U('Capacitance', [
            ['F', 'farad', 1],
            ['mF', 'millifarad', 1e-3],
            ['uF', 'microfarad', 1e-6],
            ['nF', 'nanofarad', 1e-9],
            ['pF', 'picofarad', 1e-12],
        ]),
        inductance: U('Inductance', [
            ['H', 'henry', 1],
            ['mH', 'millihenry', 1e-3],
            ['uH', 'microhenry', 1e-6],
            ['nH', 'nanohenry', 1e-9],
        ]),
        magnetic_flux: U('Magnetic flux', [
            ['Wb', 'weber', 1],
            ['mWb', 'milliweber', 1e-3],
            ['Mx', 'maxwell', 1e-8],
        ]),
        magnetic_field: U('Magnetic flux density', [
            ['T', 'tesla', 1],
            ['mT', 'millitesla', 1e-3],
            ['uT', 'microtesla', 1e-6],
            ['G', 'gauss', 1e-4],
        ]),
        data: U('Digital information', [
            ['bit', 'bit', 1],
            ['B', 'byte', 8],
            ['kbit', 'kilobit (SI)', 1e3],
            ['Mbit', 'megabit (SI)', 1e6],
            ['Gbit', 'gigabit (SI)', 1e9],
            ['kB', 'kilobyte (SI)', 8e3],
            ['MB', 'megabyte (SI)', 8e6],
            ['GB', 'gigabyte (SI)', 8e9],
            ['KiB', 'kibibyte', 8192],
            ['MiB', 'mebibyte', 8388608],
            ['GiB', 'gibibyte', 8589934592],
            ['TiB', 'tebibyte', 8796093022208],
        ]),
        data_rate: U('Data transfer rate', [
            ['bps', 'bit/second', 1],
            ['kbps', 'kilobit/second', 1e3],
            ['Mbps', 'megabit/second', 1e6],
            ['Gbps', 'gigabit/second', 1e9],
            ['Bps', 'byte/second', 8],
            ['MBps', 'megabyte/second', 8e6],
            ['MiBps', 'mebibyte/second', 8388608],
        ]),
        illuminance: U('Illuminance', [
            ['lux', 'lux', 1],
            ['klux', 'kilolux', 1e3],
            ['fc', 'foot-candle', 10.763910417],
        ]),
        radiation: U('Absorbed radiation dose', [
            ['Gy', 'gray', 1],
            ['mGy', 'milligray', 1e-3],
            ['rad', 'rad', 0.01],
        ]),
    };
    const raw = `Mathematics|Quadratic formula|x = (-b ± √(b²-4ac))/(2a)|roots polynomial discriminant|a,b,c coefficients
Mathematics|Distance formula|d = √((x₂-x₁)²+(y₂-y₁)²)|coordinate geometry|x,y coordinates
Mathematics|Circle area|A = πr²|geometry area|r radius
Mathematics|Circle circumference|C = 2πr|geometry perimeter|r radius
Mathematics|Sphere volume|V = 4πr³/3|geometry volume|r radius
Mathematics|Derivative power rule|d(xⁿ)/dx = nxⁿ⁻¹|calculus derivative|n power
Mathematics|Product rule|d(uv)/dx = u·dv/dx + v·du/dx|calculus derivative|u,v functions
Mathematics|Integration power rule|∫xⁿdx = xⁿ⁺¹/(n+1)+C|calculus integral|n ≠ -1
Mathematics|Taylor series|f(x)=Σ f⁽ⁿ⁾(a)(x-a)ⁿ/n!|calculus approximation|a expansion point
Mathematics|Euler identity|eⁱˣ = cos x + i sin x|complex numbers|i imaginary unit
Statistics|Arithmetic mean|x̄ = Σxᵢ/n|average data|n observations
Statistics|Weighted mean|x̄w = Σwᵢxᵢ/Σwᵢ|average weights|w weights
Statistics|Population variance|σ² = Σ(xᵢ-μ)²/N|dispersion|μ mean
Statistics|Sample variance|s² = Σ(xᵢ-x̄)²/(n-1)|dispersion|x̄ mean
Statistics|Standard deviation|σ = √variance|dispersion uncertainty|variance
Statistics|Z-score|z = (x-μ)/σ|normalization|μ mean; σ deviation
Statistics|Pearson correlation|r = cov(X,Y)/(σXσY)|correlation|cov covariance
Statistics|Bayes theorem|P(A|B)=P(B|A)P(A)/P(B)|probability conditional|A,B events
Statistics|Binomial probability|P(X=k)=C(n,k)pᵏ(1-p)ⁿ⁻ᵏ|probability trials|n trials; p success
Mechanics|Newton second law|F = ma|force motion dynamics|m mass; a acceleration
Mechanics|Weight|W = mg|gravity force|m mass; g gravity
Mechanics|Linear momentum|p = mv|momentum impulse|m mass; v velocity
Mechanics|Impulse|J = ∫Fdt = Δp|momentum force time|F force; p momentum
Mechanics|Kinetic energy|KE = ½mv²|energy motion|m mass; v speed
Mechanics|Potential energy|PE = mgh|energy gravity|h height
Mechanics|Work|W = Fs cosθ|energy force displacement|F force; s distance
Mechanics|Power|P = dW/dt = Fv|work rate|W work; v velocity
Mechanics|Centripetal force|F = mv²/r|circular motion|r radius
Mechanics|Angular velocity|ω = dθ/dt|rotation|θ angle
Mechanics|Rotational kinetic energy|KE = ½Iω²|rotation energy|I inertia
Mechanics|Torque|τ = r × F = Iα|rotation moment|α angular acceleration
Mechanics|Parallel-axis theorem|I = Icm + Md²|moment inertia|d offset
Mechanics|Simple harmonic motion|x = A cos(ωt+φ)|oscillation|A amplitude; φ phase
Mechanics|Pendulum period|T = 2π√(L/g)|oscillation pendulum|L length
Strength of Materials|Normal stress|σ = F/A|stress axial|F force; A area
Strength of Materials|Normal strain|ε = ΔL/L|strain deformation|L length
Strength of Materials|Hooke law|σ = Eε|elasticity modulus|E Young modulus
Strength of Materials|Shear stress|τ = V/A|shear force|V shear; A area
Strength of Materials|Poisson ratio|ν = -εlateral/εaxial|elasticity|strain
Strength of Materials|Bending equation|M/I = σ/y = E/R|beam flexure|M moment; I inertia
Strength of Materials|Torsion equation|T/J = τ/r = Gθ/L|shaft torsion|J polar inertia
Strength of Materials|Euler buckling load|Pcr = π²EI/(KL)²|column stability|K effective factor
Strength of Materials|Beam deflection simply supported center|δmax = PL³/(48EI)|beam deflection|P load; L span
Structures|Section modulus|Z = I/ymax|beam design|I inertia
Structures|Factor of safety|FoS = failure strength/allowable stress|design safety|strength stress
Structures|Truss determinacy|m+r = 2j|truss stability|members reactions joints
Structures|Hydrostatic pressure|p = ρgh|fluid retaining structure|ρ density
Fluid Mechanics|Continuity equation|A₁v₁ = A₂v₂|flow conservation|A area; v velocity
Fluid Mechanics|Mass flow rate|ṁ = ρAv|flow mass|ρ density
Fluid Mechanics|Bernoulli equation|p/ρg + v²/2g + z = constant|fluid energy head|p pressure; z elevation
Fluid Mechanics|Reynolds number|Re = ρvD/μ = vD/ν|flow regime|D diameter; μ viscosity
Fluid Mechanics|Darcy-Weisbach loss|hf = f(L/D)v²/(2g)|pipe friction|f friction factor
Fluid Mechanics|Hagen-Poiseuille flow|Q = πΔPr⁴/(8μL)|laminar pipe|r radius
Fluid Mechanics|Archimedes buoyancy|Fb = ρgVdisplaced|buoyancy|V volume
Fluid Mechanics|Drag force|Fd = ½ρCdAv²|aerodynamics drag|Cd drag coefficient
Fluid Mechanics|Lift force|Fl = ½ρClAv²|aerodynamics lift|Cl lift coefficient
Fluid Mechanics|Manning equation|Q = (1/n)AR^(2/3)S^(1/2)|open channel|n roughness
Fluid Mechanics|Pump hydraulic power|P = ρgQH/η|pump|Q flow; H head
Thermodynamics|Ideal gas law|PV = nRT|gas state|P pressure; V volume
Thermodynamics|First law closed system|ΔU = Q - W|energy conservation|Q heat; W work
Thermodynamics|Enthalpy|H = U + PV|state property|U internal energy
Thermodynamics|Sensible heat|Q = mcΔT|heat capacity|m mass; c specific heat
Thermodynamics|Latent heat|Q = mL|phase change|L latent heat
Thermodynamics|Carnot efficiency|η = 1 - Tc/Th|heat engine|absolute temperatures
Thermodynamics|COP refrigerator|COPR = QL/W|refrigeration|QL cooling; W work
Thermodynamics|Isentropic ideal gas|PVᵞ = constant|gas compression|γ heat ratio
Thermodynamics|Entropy change reversible|ΔS = ∫δQrev/T|entropy|T temperature
Heat Transfer|Fourier conduction law|Q̇ = -kA dT/dx|conduction|k conductivity
Heat Transfer|Newton cooling law|Q̇ = hA(Ts-T∞)|convection|h coefficient
Heat Transfer|Stefan-Boltzmann law|Q̇ = εσA(Ts⁴-Tsur⁴)|radiation|ε emissivity
Heat Transfer|Thermal resistance wall|R = L/(kA)|conduction resistance|L thickness
Heat Transfer|Overall heat transfer|Q̇ = UAΔTlm|heat exchanger|U overall coefficient
Heat Transfer|Log mean temperature difference|ΔTlm=(ΔT1-ΔT2)/ln(ΔT1/ΔT2)|heat exchanger|terminal differences
Electrical|Ohm law|V = IR|circuit resistance|V voltage; I current
Electrical|Electric power|P = VI = I²R = V²/R|circuit power|V,I,R
Electrical|Kirchhoff current law|ΣI = 0 at a node|circuit KCL|currents
Electrical|Kirchhoff voltage law|ΣV = 0 around a loop|circuit KVL|voltages
Electrical|Series resistance|Req = ΣRi|resistor network|Ri resistances
Electrical|Parallel resistance|1/Req = Σ(1/Ri)|resistor network|Ri resistances
Electrical|Capacitor charge|Q = CV|capacitance|C capacitance
Electrical|Capacitor energy|E = ½CV²|stored energy|C,V
Electrical|Inductor voltage|v = L di/dt|inductance transient|L inductance
Electrical|Inductor energy|E = ½LI²|stored energy|L,I
Electrical|RC time constant|τ = RC|transient circuit|R,C
Electrical|RL time constant|τ = L/R|transient circuit|L,R
Electrical|AC impedance RLC|Z = R + j(ωL - 1/ωC)|alternating current|ω angular frequency
Electrical|Resonant frequency|f₀ = 1/(2π√LC)|RLC resonance|L,C
Electrical|Transformer ratio|Vp/Vs = Np/Ns = Is/Ip|transformer|turns voltage current
Electrical|Three-phase power|P = √3 VL IL cosφ|power system|line voltage current
Electrical|Power factor|PF = cosφ = P/S|AC power|P real; S apparent
Electronics|Diode equation|I = Is(e^(V/nVT)-1)|semiconductor diode|Is saturation current
Electronics|BJT current gain|β = IC/IB|transistor|collector base current
Electronics|MOSFET saturation current|ID = ½μCox(W/L)(VGS-Vth)²|transistor|threshold voltage
Electronics|Op-amp inverting gain|Av = -Rf/Rin|amplifier|resistors
Electronics|Op-amp non-inverting gain|Av = 1 + Rf/Rg|amplifier|resistors
Electronics|Low-pass RC cutoff|fc = 1/(2πRC)|filter|R,C
Signals|Frequency and period|f = 1/T|wave signal|T period
Signals|Angular frequency|ω = 2πf|wave signal|f frequency
Signals|Wavelength|λ = v/f|wave propagation|v velocity
Signals|Sampling theorem|fs ≥ 2fmax|Nyquist sampling|maximum frequency
Signals|Fourier transform|X(f)=∫x(t)e^(-j2πft)dt|spectrum|x time signal
Signals|Signal-to-noise ratio|SNRdB = 10log₁₀(Ps/Pn)|communications|signal noise power
Control Systems|Transfer function|G(s)=Y(s)/U(s) with zero initial conditions|system response|input output
Control Systems|First-order response|y(t)=K(1-e^(-t/τ))|step response|K gain; τ time constant
Control Systems|Damping ratio|ζ = c/(2√km)|second order system|c damping
Control Systems|Natural frequency|ωn = √(k/m)|vibration control|k stiffness
Control Systems|PID controller|u=Kpe+Ki∫e dt+Kd de/dt|feedback control|error e
Materials|Bragg law|nλ = 2d sinθ|crystallography diffraction|d spacing
Materials|Hall-Petch relation|σy = σ0 + ky d^(-1/2)|grain strengthening|d grain size
Materials|Fick first law|J = -D dc/dx|diffusion|D diffusivity
Materials|Arrhenius equation|k = A e^(-Ea/RT)|reaction rate materials|Ea activation energy
Chemistry|Molarity|M = moles solute/litres solution|concentration|moles volume
Chemistry|Beer-Lambert law|A = εbc|spectroscopy absorbance|ε absorptivity
Chemistry|Nernst equation|E = E° - RT lnQ/(nF)|electrochemistry|Q reaction quotient
Computer Science|Binary search complexity|T(n) = O(log n)|algorithm complexity search|n input size
Computer Science|Merge sort complexity|T(n) = 2T(n/2)+O(n)=O(n log n)|algorithm sorting|n input size
Computer Science|Shannon entropy|H(X) = -Σp(x)log₂p(x)|information theory|p probability
Computer Science|RSA relation|c = mᵉ mod n; m = cᵈ mod n|cryptography|keys e,d,n
Computer Science|Amdahl law|Speedup = 1/((1-p)+p/s)|parallel computing|p parallel fraction
Machine Learning|Linear regression|ŷ = β₀ + Σβᵢxᵢ|prediction regression|β coefficients
Machine Learning|Mean squared error|MSE = Σ(yᵢ-ŷᵢ)²/n|loss regression|actual predicted
Machine Learning|Logistic sigmoid|σ(z)=1/(1+e⁻ᶻ)|classification|z score
Machine Learning|Binary cross entropy|L=-[ylnp+(1-y)ln(1-p)]|classification loss|p probability
Machine Learning|Gradient descent|θ := θ - α∇J(θ)|optimization|α learning rate
Aerospace|Mach number|M = v/a|compressible flow|v speed; a sound speed
Aerospace|Rocket equation|Δv = ve ln(m0/mf)|propulsion|ve exhaust velocity
Aerospace|Aircraft lift|L = ½ρV²SCL|aerodynamics|S wing area
Aerospace|Aircraft drag|D = ½ρV²SCD|aerodynamics|CD drag coefficient
Civil Engineering|Concrete characteristic strength|fck = value below which 5% results fall|concrete design|compressive strength
Civil Engineering|Soil void ratio|e = Vv/Vs|geotechnical soil|void solid volume
Civil Engineering|Porosity|n = Vv/V = e/(1+e)|geotechnical soil|void ratio
Civil Engineering|Degree of saturation|Sr = Vw/Vv|geotechnical soil|water void volume
Civil Engineering|Terzaghi bearing capacity|qult=cNc+qNq+0.5γBNγ|foundation soil|c cohesion; B width
Surveying|Reduced level height of instrument|RL = HI - staff reading|levelling survey|HI instrument height
Surveying|Tacheometry distance|D = Ks cos²θ + C cosθ|survey distance|K,C constants`;
    window.ENOUGHEDU_FORMULAS = raw.split('\n').map((line) => {
        const [category, name, formula, keywords, variables] = line.split('|');
        return { category, name, formula, keywords, variables };
    });
})();
