import { useEffect, useState } from "react";
import axios from "axios";
import { Input } from "@/components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";

interface Domain {
    domain: string;
    rank: number;
}

interface PaginationLink {
    label: string;
    url: string | null;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

interface PaginationState {
    links: PaginationLink[];
    meta: PaginationMeta;
}

const Domains = () => {
    const [domains, setDomains] = useState<Domain[]>([]);
    const [pagination, setPagination] = useState<PaginationState | null>(null);
    const [search, setSearch] = useState("");
    const [page, setPage] = useState(1);

    useEffect(() => {
        fetchDomains();
    }, [search, page]);

    const fetchDomains = async () => {
        try {
            const response = await axios.get("/api/domains", {
                params: { page, search },
            });

            const payload = response.data;

            setDomains(Array.isArray(payload.data) ? payload.data : []);
            setPagination({
                links: Array.isArray(payload.meta?.links) ? payload.meta.links : [],
                meta: payload.meta,
            });
        } catch (error) {
            console.error("Error fetching domains:", error);
            setDomains([]);
            setPagination(null);
        }
    };

    const handlePaginationClick = (url: string | null) => {
        if (!url) return;
        const params = new URLSearchParams(url.split("?")[1]);
        const newPage = parseInt(params.get("page") || "1", 10);
        setPage(newPage);
    };

    return (
        <div className="max-w-5xl mx-auto p-6 space-y-6">
            <Card>
                <CardContent className="p-6 space-y-4">
                    <h1 className="text-2xl font-bold">Domain List</h1>

                    <Input
                        type="text"
                        placeholder="Search by domain name..."
                        value={search}
                        onChange={(e) => {
                            setSearch(e.target.value);
                            setPage(1);
                        }}
                    />

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Domain</TableHead>
                                <TableHead>Rank</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {domains.length > 0 ? (
                                domains.map((domain) => (
                                    <TableRow key={domain.domain}>
                                        <TableCell>{domain.domain}</TableCell>
                                        <TableCell>{domain.rank}</TableCell>
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={2}>No domains found.</TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>

                    <div className="flex flex-wrap gap-2">
                        {pagination?.links?.map((link, index) => (
                            <Button
                                key={index}
                                variant={link.active ? "default" : "outline"}
                                disabled={!link.url}
                                onClick={() => handlePaginationClick(link.url)}
                            >
                                <span dangerouslySetInnerHTML={{ __html: link.label }} />
                            </Button>
                        ))}
                    </div>
                </CardContent>
            </Card>
        </div>
    );
};

export default Domains;
