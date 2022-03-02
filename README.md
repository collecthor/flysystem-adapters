# Flysystem Meta Adapters
Flysystem uses adapters as an abstraction for storage implementations.
This library contains a set of meta-adapters that take 1 or more adapters as their input and expose them as a normal adapter.

## OverlayAdapter

While Flysystem has a mount manager, this requires explicit knowledge of the mounts on the consumer side. For separating
concerns this is not ideal.
Imagine just exposing an adapter to your code (via a `FileSystem`) and having the magic of different backends be handled
on the configuration side.

The `OverlayAdapter` for example could allow you to have any `Flysystem` compatible file browser and use it to browse a virtual filesystem 
that consists of both real files on S3 storage, real files on local storage and virtual directories pulled from your database.
