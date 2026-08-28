import { Map, MapMarker, MapTileLayer } from "@/components/ui/map";
import type { LatLngExpression } from "leaflet";
import { MapPin } from "lucide-react";

type CoordinateBoxProps = {
    className?: string;
};

export function CoordinateBox({ className = "" }: CoordinateBoxProps) {
    const location = {
        name: "Matterhorn",
        coordinates: [-6.37634, 106.7444] satisfies LatLngExpression,
        icon: <MapPin className="text-primary" size={24} />,
    };

    return (
        <Map center={location.coordinates} zoom={20} className={className}>
            <MapTileLayer />
            <MapMarker
                key={location.name}
                position={location.coordinates}
                icon={location.icon}
            />
        </Map>
    );
}
