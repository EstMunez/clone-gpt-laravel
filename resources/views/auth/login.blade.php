<!-- resources/views/auth/login.blade.php -->
<form method="POST" action="{{ route('login') }}">
    @csrf
    <label>Email</label>
    <input type="email" name="email" required autofocus />
    <label>Mot de passe</label>
    <input type="password" name="password" required />
    <button type="submit">Se connecter</button>
</form>
