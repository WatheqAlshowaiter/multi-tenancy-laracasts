<div>
    <input wire:model="name" type="text">
    <button wire:click="submit">Save</button>

    @if ($success)
        <div>Saved!</div>
    @endif
</div>
